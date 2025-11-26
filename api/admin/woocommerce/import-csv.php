<?php
/**
 * WooCommerce CSV Import API
 * 
 * Handles CSV file upload and imports products from WooCommerce format
 */

require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

use App\Database\Connection;
use App\Support\Id;
use App\Http\JsonResponse;

// Check admin authentication
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'type' => 'error',
        'message' => 'Unauthorized. Please log in to continue.',
        'level' => 'error'
    ]);
    exit;
}

// Clear any output buffers for streaming
while (ob_get_level()) {
    ob_end_clean();
}

// Check if feature is enabled
try {
    $db = Connection::getInstance();
    $featureEnabled = $db->prepare("SELECT enabled FROM optional_features WHERE feature_key = 'woocommerce_csv_import'");
    $featureEnabled->execute();
    $isEnabled = $featureEnabled->fetchColumn() == 1;
    
    if (!$isEnabled) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'type' => 'error',
            'message' => 'WooCommerce CSV Import feature is not enabled. Please enable it in Optional Features.',
            'level' => 'error'
        ]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'type' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'level' => 'error'
    ]);
    exit;
}

// Set headers for streaming (must be before any output)
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header('Connection: keep-alive');

// Ensure no output buffering interferes
if (ob_get_level()) {
    ob_end_clean();
}

function sendProgress($percent, $message = '') {
    echo json_encode([
        'type' => 'progress',
        'percent' => $percent,
        'message' => $message
    ]) . "\n";
    flush();
}

function sendLog($message, $level = 'info') {
    echo json_encode([
        'type' => 'log',
        'level' => $level,
        'message' => $message
    ]) . "\n";
    flush();
}

function sendComplete($stats) {
    echo json_encode([
        'type' => 'complete',
        'stats' => $stats
    ]) . "\n";
    flush();
}

try {
    // Check file upload
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        sendLog('❌ No file uploaded or upload error', 'error');
        sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
        exit;
    }
    
    $file = $_FILES['csv_file'];
    $options = [
        'download_images' => isset($_POST['download_images']) && $_POST['download_images'] == '1',
        'create_categories' => isset($_POST['create_categories']) && $_POST['create_categories'] == '1',
        'skip_duplicates' => isset($_POST['skip_duplicates']) && $_POST['skip_duplicates'] == '1',
    ];
    
    sendLog('📁 Reading CSV file...', 'info');
    sendProgress(5);
    
    // Read CSV file
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        sendLog('❌ Cannot open CSV file', 'error');
        sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
        exit;
    }
    
    // Read header row
    $headers = fgetcsv($handle);
    if (!$headers) {
        sendLog('❌ Invalid CSV format - no headers found', 'error');
        fclose($handle);
        sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
        exit;
    }
    
    // Normalize headers (lowercase, trim)
    $headers = array_map(function($h) {
        return strtolower(trim($h));
    }, $headers);
    
    sendLog('✅ CSV file loaded. Found ' . count($headers) . ' columns', 'success');
    sendProgress(10);
    
    // Map WooCommerce columns to our fields
    $fieldMap = [
        'name' => ['name', 'product name', 'title', 'post_title'],
        'slug' => ['slug', 'post_name'],
        'sku' => ['sku'],
        'summary' => ['short description', 'excerpt', 'post_excerpt'],
        'description' => ['description', 'post_content'],
        'price' => ['regular_price', 'price', '_regular_price'],
        'image' => ['images', 'image', 'featured_image', '_thumbnail_id'],
        'category' => ['categories', 'product_cat', 'product_categories'],
        'status' => ['status', 'post_status'],
    ];
    
    // Find column indices
    $columnMap = [];
    foreach ($fieldMap as $ourField => $wcFields) {
        foreach ($wcFields as $wcField) {
            $index = array_search($wcField, $headers);
            if ($index !== false) {
                $columnMap[$ourField] = $index;
                break;
            }
        }
    }
    
    if (empty($columnMap)) {
        sendLog('❌ Cannot map CSV columns. Please check CSV format.', 'error');
        fclose($handle);
        sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
        exit;
    }
    
    sendLog('✅ Column mapping complete', 'success');
    sendProgress(15);
    
    // Import statistics
    $stats = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0,
        'categories' => 0
    ];
    
    // Get all existing categories
    $existingCategories = [];
    $catStmt = $db->query("SELECT id, name, slug FROM categories");
    while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
        $existingCategories[strtolower($cat['name'])] = $cat;
        $existingCategories[strtolower($cat['slug'])] = $cat;
    }
    
    // Process rows
    $rowCount = 0;
    $totalRows = 0;
    
    // Count total rows first
    while (fgetcsv($handle)) {
        $totalRows++;
    }
    rewind($handle);
    fgetcsv($handle); // Skip header
    
    sendLog("📊 Processing $totalRows products...", 'info');
    
    while (($row = fgetcsv($handle)) !== false) {
        $rowCount++;
        $percent = 15 + (($rowCount / $totalRows) * 80);
        sendProgress($percent);
        
        try {
            // Extract data
            $data = [];
            foreach ($columnMap as $field => $index) {
                $data[$field] = isset($row[$index]) ? trim($row[$index]) : '';
            }
            
            // Skip empty rows
            if (empty($data['name']) && empty($data['sku'])) {
                $stats['skipped']++;
                continue;
            }
            
            // Generate product ID
            $productId = Id::prefixed('prod');
            
            // Check for duplicates (by SKU)
            if (!empty($data['sku']) && $options['skip_duplicates']) {
                $checkStmt = $db->prepare("SELECT id FROM products WHERE sku = ?");
                $checkStmt->execute([$data['sku']]);
                if ($checkStmt->fetchColumn()) {
                    sendLog("⏭️  Skipping duplicate: {$data['name']} (SKU: {$data['sku']})", 'info');
                    $stats['skipped']++;
                    continue;
                }
            }
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
            }
            
            // Ensure unique slug
            $slug = $data['slug'];
            $slugCount = 0;
            while (true) {
                $checkStmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
                $checkStmt->execute([$slug]);
                if (!$checkStmt->fetchColumn()) break;
                $slugCount++;
                $slug = $data['slug'] . '-' . $slugCount;
            }
            $data['slug'] = $slug;
            
            // Handle category
            $categoryId = null;
            if (!empty($data['category'])) {
                $categoryNames = array_map('trim', explode('|', $data['category']));
                $categoryName = $categoryNames[0]; // Use first category
                
                // Find or create category
                $catKey = strtolower($categoryName);
                if (isset($existingCategories[$catKey])) {
                    $categoryId = $existingCategories[$catKey]['id'];
                } elseif ($options['create_categories']) {
                    // Create new category
                    $catId = Id::prefixed('cat');
                    $catSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $categoryName)));
                    
                    // Ensure unique category slug
                    $catSlugCount = 0;
                    $finalCatSlug = $catSlug;
                    while (true) {
                        $checkStmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
                        $checkStmt->execute([$finalCatSlug]);
                        if (!$checkStmt->fetchColumn()) break;
                        $catSlugCount++;
                        $finalCatSlug = $catSlug . '-' . $catSlugCount;
                    }
                    
                    $catStmt = $db->prepare("
                        INSERT INTO categories (id, name, slug, createdAt, updatedAt)
                        VALUES (?, ?, ?, NOW(), NOW())
                    ");
                    $catStmt->execute([$catId, $categoryName, $finalCatSlug]);
                    
                    $existingCategories[$catKey] = ['id' => $catId, 'name' => $categoryName, 'slug' => $finalCatSlug];
                    $categoryId = $catId;
                    $stats['categories']++;
                    sendLog("📦 Created category: $categoryName", 'success');
                } else {
                    sendLog("⚠️  Category not found: $categoryName (skipping product)", 'error');
                    $stats['skipped']++;
                    continue;
                }
            }
            
            // Use default category if none specified
            if (!$categoryId) {
                $defaultCat = $db->query("SELECT id FROM categories LIMIT 1")->fetchColumn();
                if ($defaultCat) {
                    $categoryId = $defaultCat;
                } else {
                    sendLog("❌ No categories available. Please create at least one category.", 'error');
                    $stats['errors']++;
                    continue;
                }
            }
            
            // Handle price
            $price = 0;
            if (!empty($data['price'])) {
                $price = floatval(preg_replace('/[^0-9.]/', '', $data['price']));
            }
            
            // Handle status
            $status = 'PUBLISHED';
            if (!empty($data['status'])) {
                $wcStatus = strtolower($data['status']);
                if ($wcStatus === 'publish' || $wcStatus === 'published') {
                    $status = 'PUBLISHED';
                } else {
                    $status = 'DRAFT';
                }
            }
            
            // Handle image
            $heroImage = '';
            if (!empty($data['image'])) {
                $imageUrl = trim($data['image']);
                
                if ($options['download_images'] && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    // Download image
                    $imagePath = downloadImage($imageUrl, $productId);
                    if ($imagePath) {
                        $heroImage = $imagePath;
                        sendLog("📥 Downloaded image for: {$data['name']}", 'success');
                    } else {
                        $heroImage = $imageUrl; // Fallback to URL
                    }
                } else {
                    $heroImage = $imageUrl;
                }
            }
            
            // Insert product
            $stmt = $db->prepare("
                INSERT INTO products (
                    id, name, slug, sku, summary, description, price, heroImage, 
                    categoryId, status, createdAt, updatedAt
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $productId,
                $data['name'] ?? 'Untitled Product',
                $data['slug'],
                $data['sku'] ?? null,
                $data['summary'] ?? '',
                $data['description'] ?? '',
                $price,
                $heroImage,
                $categoryId,
                $status
            ]);
            
            $stats['imported']++;
            sendLog("✅ Imported: {$data['name']}", 'success');
            
        } catch (Exception $e) {
            $stats['errors']++;
            sendLog("❌ Error importing row $rowCount: " . $e->getMessage(), 'error');
        }
    }
    
    fclose($handle);
    
    sendProgress(100);
    sendLog("🎉 Import complete!", 'success');
    sendComplete($stats);
    
} catch (Exception $e) {
    sendLog("❌ Fatal error: " . $e->getMessage(), 'error');
    sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
}

/**
 * Download image from URL
 */
function downloadImage($url, $productId) {
    try {
        // Create uploads directory if not exists
        $basePath = dirname(__DIR__, 3);
        $uploadDir = $basePath . '/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Get file extension
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = 'jpg';
        }
        
        $filename = 'prod_' . $productId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Download file
        $imageData = @file_get_contents($url);
        if ($imageData === false) {
            return null;
        }
        
        // Save file
        if (file_put_contents($filepath, $imageData) !== false) {
            return '/uploads/products/' . $filename;
        }
        
        return null;
    } catch (Exception $e) {
        return null;
    }
}

