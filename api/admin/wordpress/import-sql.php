<?php
/**
 * WordPress SQL Import API
 * 
 * Imports products directly from WordPress/WooCommerce database
 */

// CRITICAL: Start output buffering IMMEDIATELY - before ANYTHING else
while (ob_get_level() > 0) {
    @ob_end_clean();
}
@ob_start();

// Suppress any errors/warnings that might output HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Skip CacheControl for API endpoints
if (!defined('DISABLE_CACHE_CONTROL')) {
    define('DISABLE_CACHE_CONTROL', true);
}

require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../config/database.php';

use App\Database\Connection;
use App\Support\Id;

// Check admin authentication
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Clear any output before sending JSON error
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    http_response_code(401);
    echo json_encode([
        'type' => 'error',
        'message' => 'Unauthorized. Please log in to continue.',
        'level' => 'error'
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Clear any output buffers for streaming
while (ob_get_level() > 0) {
    @ob_end_clean();
}

// Check if feature is enabled
try {
    $db = Connection::getInstance();
    $featureEnabled = $db->prepare("SELECT enabled FROM optional_features WHERE feature_key = 'wordpress_sql_import'");
    $featureEnabled->execute();
    $isEnabled = $featureEnabled->fetchColumn() == 1;
    
    if (!$isEnabled) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'type' => 'error',
            'message' => 'WordPress SQL Import feature is not enabled.',
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

// Set headers for streaming
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header('Connection: keep-alive');

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
    // Get WordPress connection details
    $wpHost = $_POST['wp_host'] ?? '';
    $wpDatabase = $_POST['wp_database'] ?? '';
    $wpUsername = $_POST['wp_username'] ?? '';
    $wpPassword = $_POST['wp_password'] ?? '';
    $wpPrefix = $_POST['wp_prefix'] ?? 'wp_';
    
    $options = [
        'download_images' => isset($_POST['download_images']) && $_POST['download_images'] == '1',
        'create_categories' => isset($_POST['create_categories']) && $_POST['create_categories'] == '1',
        'skip_duplicates' => isset($_POST['skip_duplicates']) && $_POST['skip_duplicates'] == '1',
        'import_variations' => isset($_POST['import_variations']) && $_POST['import_variations'] == '1',
    ];
    
    if (empty($wpHost) || empty($wpDatabase) || empty($wpUsername)) {
        sendLog('❌ Missing WordPress database credentials', 'error');
        sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
        exit;
    }
    
    sendLog('🔌 Connecting to WordPress database...', 'info');
    sendProgress(5);
    
    // Connect to WordPress database
    // Handle port in host (e.g., "host:3306" or "host,3306")
    $hostParts = explode(':', $wpHost);
    $dbHost = $hostParts[0];
    $dbPort = isset($hostParts[1]) ? (int)$hostParts[1] : 3306;
    
    // Build DSN with optional port
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$wpDatabase};charset=utf8mb4";
    $wpDb = new PDO($dsn, $wpUsername, $wpPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30, // 30 second timeout for imports
    ]);
    
    sendLog('✅ Connected to WordPress database', 'success');
    sendProgress(10);
    
    // Get all products from WordPress
    $postsTable = $wpPrefix . 'posts';
    $postmetaTable = $wpPrefix . 'postmeta';
    $termsTable = $wpPrefix . 'terms';
    $termTaxonomyTable = $wpPrefix . 'term_taxonomy';
    $termRelationshipsTable = $wpPrefix . 'term_relationships';
    
    $productQuery = "
        SELECT p.ID, p.post_title, p.post_name, p.post_content, p.post_excerpt, p.post_status
        FROM {$postsTable} p
        WHERE p.post_type = 'product'
        AND p.post_status IN ('publish', 'private')
        ORDER BY p.ID
    ";
    
    if (!$options['import_variations']) {
        // Exclude variations
        $productQuery = "
            SELECT p.ID, p.post_title, p.post_name, p.post_content, p.post_excerpt, p.post_status
            FROM {$postsTable} p
            LEFT JOIN {$postmetaTable} pm ON p.ID = pm.post_id AND pm.meta_key = '_product_type'
            WHERE p.post_type = 'product'
            AND p.post_status IN ('publish', 'private')
            AND (pm.meta_value IS NULL OR pm.meta_value != 'variable')
            ORDER BY p.ID
        ";
    }
    
    $wpProducts = $wpDb->query($productQuery)->fetchAll();
    $totalProducts = count($wpProducts);
    
    if ($totalProducts == 0) {
        // Check if database has any posts at all
        $totalPostsCheck = $wpDb->query("SELECT COUNT(*) FROM {$postsTable}")->fetchColumn();
        
        if ($totalPostsCheck == 0) {
            sendLog("⚠️  Database appears to be empty (0 posts found)", 'error');
            sendLog("💡 Possible issues:", 'info');
            sendLog("   1. Database might be new/empty", 'info');
            sendLog("   2. Wrong table prefix (current: {$wpPrefix})", 'info');
            sendLog("   3. Wrong database name", 'info');
            sendLog("   4. WordPress might not be installed yet", 'info');
            sendLog("", 'info');
            sendLog("🔍 Checking available tables...", 'info');
            
            // List tables
            try {
                $allTables = $wpDb->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                if (empty($allTables)) {
                    sendLog("   ❌ No tables found in database!", 'error');
                } else {
                    sendLog("   ✅ Found " . count($allTables) . " tables:", 'success');
                    foreach (array_slice($allTables, 0, 10) as $table) {
                        sendLog("      - {$table}", 'info');
                    }
                    if (count($allTables) > 10) {
                        sendLog("      ... and " . (count($allTables) - 10) . " more", 'info');
                    }
                }
            } catch (Exception $e) {
                sendLog("   ⚠️  Could not list tables: " . $e->getMessage(), 'error');
            }
            
            sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
            exit;
        } else {
            // Has posts but no products
            sendLog("⚠️  Found {$totalPostsCheck} posts, but 0 products", 'error');
            sendLog("💡 Checking post types...", 'info');
            
            try {
                $postTypes = $wpDb->query("
                    SELECT post_type, COUNT(*) as count 
                    FROM {$postsTable} 
                    GROUP BY post_type 
                    ORDER BY count DESC
                    LIMIT 10
                ")->fetchAll();
                
                if (!empty($postTypes)) {
                    sendLog("   Available post types:", 'info');
                    foreach ($postTypes as $pt) {
                        sendLog("      - {$pt['post_type']}: {$pt['count']} posts", 'info');
                    }
                    sendLog("", 'info');
                    sendLog("💡 If you want to import posts instead of products, we can modify the import.", 'info');
                }
            } catch (Exception $e) {
                // Ignore
            }
            
            sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
            exit;
        }
    }
    
    sendLog("📊 Found {$totalProducts} products to import", 'info');
    sendProgress(15);
    
    // Get all existing categories
    $existingCategories = [];
    $catStmt = $db->query("SELECT id, name, slug FROM categories");
    while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
        $existingCategories[strtolower($cat['name'])] = $cat;
        $existingCategories[strtolower($cat['slug'])] = $cat;
    }
    
    // Import statistics
    $stats = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0,
        'categories' => 0
    ];
    
    // Process each product
    foreach ($wpProducts as $index => $wpProduct) {
        $percent = 15 + (($index + 1) / $totalProducts) * 80;
        sendProgress($percent);
        
        try {
            $productId = $wpProduct['ID'];
            
            // Get product meta data
            $metaQuery = $wpDb->prepare("
                SELECT meta_key, meta_value 
                FROM {$postmetaTable} 
                WHERE post_id = ?
            ");
            $metaQuery->execute([$productId]);
            $metaData = [];
            while ($meta = $metaQuery->fetch(PDO::FETCH_ASSOC)) {
                $metaData[$meta['meta_key']] = $meta['meta_value'];
            }
            
            // Extract product data
            $sku = $metaData['_sku'] ?? '';
            $price = isset($metaData['_regular_price']) ? floatval($metaData['_regular_price']) : 0;
            if ($price == 0 && isset($metaData['_price'])) {
                $price = floatval($metaData['_price']);
            }
            
            // Generate slug first (needed for duplicate check)
            $slug = $wpProduct['post_name'] ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $wpProduct['post_title'])));
            $productName = trim($wpProduct['post_title']);
            
            // Check for duplicates - ALWAYS check (multiple methods)
            $isDuplicate = false;
            $duplicateReason = '';
            
            // Method 1: Check by SKU (if SKU exists)
            if (!empty($sku)) {
                $checkStmt = $db->prepare("SELECT id, name FROM products WHERE sku = ? AND sku IS NOT NULL AND sku != ''");
                $checkStmt->execute([$sku]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $isDuplicate = true;
                    $duplicateReason = "SKU: {$sku} (existing: {$existing['name']})";
                }
            }
            
            // Method 2: Check by slug (if not already duplicate)
            if (!$isDuplicate) {
                $checkStmt = $db->prepare("SELECT id, name FROM products WHERE slug = ?");
                $checkStmt->execute([$slug]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $isDuplicate = true;
                    $duplicateReason = "Slug: {$slug} (existing: {$existing['name']})";
                }
            }
            
            // Method 3: Check by exact name match (if not already duplicate)
            if (!$isDuplicate && !empty($productName)) {
                $checkStmt = $db->prepare("SELECT id, sku FROM products WHERE name = ?");
                $checkStmt->execute([$productName]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $isDuplicate = true;
                    $existingSku = $existing['sku'] ? " (SKU: {$existing['sku']})" : '';
                    $duplicateReason = "Name: {$productName}{$existingSku}";
                }
            }
            
            // Skip duplicate if found
            if ($isDuplicate) {
                if ($options['skip_duplicates']) {
                    sendLog("⏭️  Skipping duplicate: {$productName} - {$duplicateReason}", 'info');
                    $stats['skipped']++;
                    continue;
                } else {
                    // If skip_duplicates is off, we'll update the slug to make it unique
                    sendLog("⚠️  Duplicate found: {$productName} - {$duplicateReason}. Making slug unique...", 'info');
                }
            }
            
            // Generate product ID
            $newProductId = Id::prefixed('prod');
            
            // Ensure unique slug (if duplicate found and skip_duplicates is off, or if slug already exists)
            $finalSlug = $slug;
            $slugCount = 0;
            while (true) {
                $checkStmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
                $checkStmt->execute([$finalSlug]);
                if (!$checkStmt->fetchColumn()) break;
                $slugCount++;
                $finalSlug = $slug . '-' . $slugCount;
            }
            
            // If slug was changed, log it
            if ($finalSlug !== $slug) {
                sendLog("📝 Slug adjusted: {$slug} → {$finalSlug} (to avoid duplicate)", 'info');
            }
            
            // Get product categories
            $categoryId = null;
            $categoryQuery = $wpDb->prepare("
                SELECT t.name, t.slug
                FROM {$termsTable} t
                INNER JOIN {$termTaxonomyTable} tt ON t.term_id = tt.term_id
                INNER JOIN {$termRelationshipsTable} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tr.object_id = ? AND tt.taxonomy = 'product_cat'
                LIMIT 1
            ");
            $categoryQuery->execute([$productId]);
            $wpCategory = $categoryQuery->fetch();
            
            if ($wpCategory) {
                $categoryName = $wpCategory['name'];
                $catKey = strtolower($categoryName);
                
                if (isset($existingCategories[$catKey])) {
                    $categoryId = $existingCategories[$catKey]['id'];
                } elseif ($options['create_categories']) {
                    // Create new category
                    $catId = Id::prefixed('cat');
                    $catSlug = $wpCategory['slug'] ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $categoryName)));
                    
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
            
            // Get product image
            $heroImage = '';
            $imageId = $metaData['_thumbnail_id'] ?? null;
            if ($imageId) {
                $imageQuery = $wpDb->prepare("
                    SELECT guid 
                    FROM {$postsTable} 
                    WHERE ID = ? AND post_type = 'attachment'
                ");
                $imageQuery->execute([$imageId]);
                $image = $imageQuery->fetch();
                if ($image && !empty($image['guid'])) {
                    $imageUrl = $image['guid'];
                    
                    if ($options['download_images'] && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        sendLog("📥 Downloading image for: {$wpProduct['post_title']}...", 'info');
                        $imagePath = downloadImage($imageUrl, $newProductId, $wpProduct['post_title']);
                        if ($imagePath) {
                            $heroImage = $imagePath;
                        } else {
                            $heroImage = $imageUrl;
                            sendLog("⚠️  Could not download image for: {$wpProduct['post_title']} (using remote URL)", 'info');
                        }
                    } else {
                        $heroImage = $imageUrl;
                    }
                }
            }
            
            // Handle status
            $status = ($wpProduct['post_status'] === 'publish') ? 'PUBLISHED' : 'DRAFT';
            
            // Check one more time before insert (race condition protection)
            $finalCheck = false;
            if (!empty($sku)) {
                $checkStmt = $db->prepare("SELECT id FROM products WHERE sku = ? AND sku IS NOT NULL AND sku != ''");
                $checkStmt->execute([$sku]);
                $finalCheck = $checkStmt->fetchColumn();
            }
            
            if (!$finalCheck) {
                // Insert product
                $stmt = $db->prepare("
                    INSERT INTO products (
                        id, name, slug, sku, summary, description, price, heroImage, 
                        categoryId, status, createdAt, updatedAt
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $stmt->execute([
                    $newProductId,
                    $wpProduct['post_title'] ?: 'Untitled Product',
                    $finalSlug,
                    $sku ?: null,
                    $wpProduct['post_excerpt'] ?? '',
                    $wpProduct['post_content'] ?? '',
                    $price,
                    $heroImage,
                    $categoryId,
                    $status
                ]);
                
                $stats['imported']++;
                $skuInfo = !empty($sku) ? " (SKU: {$sku})" : '';
                sendLog("✅ Imported: {$wpProduct['post_title']}{$skuInfo}", 'success');
            } else {
                // Last-second duplicate detected
                sendLog("⏭️  Skipping duplicate (last check): {$wpProduct['post_title']} (SKU: {$sku})", 'info');
                $stats['skipped']++;
            }
            
        } catch (Exception $e) {
            $stats['errors']++;
            sendLog("❌ Error importing product ID {$wpProduct['ID']}: " . $e->getMessage(), 'error');
        }
    }
    
    sendProgress(100);
    sendLog("🎉 Import complete!", 'success');
    sendComplete($stats);
    
} catch (PDOException $e) {
    $message = 'Database connection failed';
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        $message = 'Access denied. Check username and password.';
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        $message = 'Database not found. Check database name.';
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        $message = 'Cannot connect to host. Check host address.';
    }
    
    sendLog("❌ $message: " . $e->getMessage(), 'error');
    sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
} catch (Exception $e) {
    sendLog("❌ Fatal error: " . $e->getMessage(), 'error');
    sendComplete(['imported' => 0, 'skipped' => 0, 'errors' => 1, 'categories' => 0]);
}

/**
 * Download and optimize image from URL
 */
function downloadImage($url, $productId, $productName = '') {
    try {
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            if (function_exists('sendLog')) {
                sendLog("⚠️  GD extension not available. Images will be saved without optimization.", 'error');
            }
            // Fallback to simple download without optimization
            return downloadImageSimple($url, $productId);
        }
        
        $basePath = dirname(__DIR__, 3);
        $uploadDir = $basePath . '/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Download image with progress tracking
        $filename = basename(parse_url($url, PHP_URL_PATH));
        if (function_exists('sendLog')) {
            sendLog("   ⬇️  Downloading: {$filename} (0%)", 'info');
        }
        
        // Use cURL for progress tracking
        $progress = ['lastPercent' => 0];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($filename, &$progress) {
            if ($download_size > 0 && function_exists('sendLog')) {
                $percent = round(($downloaded / $download_size) * 100);
                // Only log at 10% intervals to avoid spam
                if ($percent >= $progress['lastPercent'] + 10 || $percent >= 100) {
                    sendLog("   ⬇️  Downloading: {$filename} ({$percent}%)", 'info');
                    $progress['lastPercent'] = $percent;
                }
            } elseif ($download_size == 0 && $downloaded > 0 && function_exists('sendLog')) {
                // Size unknown, show downloaded amount
                $downloadedKB = round($downloaded / 1024, 2);
                sendLog("   ⬇️  Downloading: {$filename} ({$downloadedKB}KB)", 'info');
            }
            return 0;
        });
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($imageData === false || $httpCode !== 200) {
            if (function_exists('sendLog')) {
                sendLog("   ❌ Download failed (HTTP {$httpCode})", 'error');
            }
            return null;
        }
        
        if (function_exists('sendLog')) {
            $sizeKB = round(strlen($imageData) / 1024, 2);
            $sizeMB = round(strlen($imageData) / 1024 / 1024, 2);
            $sizeDisplay = $sizeMB >= 1 ? "{$sizeMB}MB" : "{$sizeKB}KB";
            sendLog("   ✅ Downloaded: {$sizeDisplay} (100%)", 'success');
        }
        
        // Create temporary file
        $tmpFile = tempnam(sys_get_temp_dir(), 'wp_import_');
        file_put_contents($tmpFile, $imageData);
        
        // Get image info
        if (function_exists('sendLog')) {
            sendLog("   🔍 Analyzing image...", 'info');
        }
        
        $imageInfo = @getimagesize($tmpFile);
        if ($imageInfo === false) {
            @unlink($tmpFile);
            if (function_exists('sendLog')) {
                sendLog("   ❌ Invalid image file", 'error');
            }
            return null;
        }
        
        $mimeType = $imageInfo['mime'];
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $originalSize = filesize($tmpFile);
        $originalSizeKB = round($originalSize / 1024, 2);
        $originalSizeMB = round($originalSize / 1024 / 1024, 2);
        $originalSizeDisplay = $originalSizeMB >= 1 ? "{$originalSizeMB}MB" : "{$originalSizeKB}KB";
        
        if (function_exists('sendLog')) {
            sendLog("   📊 Original: {$originalWidth}x{$originalHeight}px, {$originalSizeDisplay}", 'info');
        }
        
        // Determine output format (prefer JPEG for smaller size)
        $outputFormat = 'jpg';
        $outputExtension = 'jpg';
        if ($mimeType === 'image/png') {
            $outputFormat = 'png';
            $outputExtension = 'png';
        } elseif ($mimeType === 'image/webp') {
            $outputFormat = 'webp';
            $outputExtension = 'webp';
        }
        
        // Maximum dimensions (reduce large images to save space)
        $maxWidth = 1920;  // Full HD width (good for web)
        $maxHeight = 1920; // Full HD height
        $quality = 85;     // JPEG quality (85% is good balance between quality and size)
        $targetMaxSize = 500 * 1024; // Target: 500KB max per image
        
        // Calculate new dimensions (maintain aspect ratio)
        $newWidth = $originalWidth;
        $newHeight = $originalHeight;
        $needsResize = false;
        
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
            $needsResize = true;
            
            $resizePercent = round((($originalWidth * $originalHeight) - ($newWidth * $newHeight)) / ($originalWidth * $originalHeight) * 100);
            
            if (function_exists('sendLog')) {
                sendLog("   🔄 Resizing: {$originalWidth}x{$originalHeight} → {$newWidth}x{$newHeight} ({$resizePercent}% reduction)", 'info');
            }
        } else {
            if (function_exists('sendLog')) {
                sendLog("   ✓ Size OK, optimizing quality only...", 'info');
            }
        }
        
        // Create image resource from temporary file
        if (function_exists('sendLog')) {
            sendLog("   🖼️  Processing {$mimeType} image... (0%)", 'info');
        }
        
        $sourceImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = @imagecreatefromjpeg($tmpFile);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($tmpFile);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($tmpFile);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = @imagecreatefromwebp($tmpFile);
                }
                break;
        }
        
        if (!$sourceImage) {
            @unlink($tmpFile);
            if (function_exists('sendLog')) {
                sendLog("   ❌ Failed to create image resource", 'error');
            }
            return null;
        }
        
        if (function_exists('sendLog')) {
            sendLog("   🖼️  Processing {$mimeType} image... (25%)", 'info');
        }
        
        // Create resized image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG/GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        if (function_exists('sendLog')) {
            sendLog("   🖼️  Processing {$mimeType} image... (50%)", 'info');
        }
        
        // Resize image
        if ($needsResize) {
            if (function_exists('sendLog')) {
                sendLog("   ✂️  Resampling image... (60%)", 'info');
            }
        }
        
        imagecopyresampled(
            $resizedImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );
        
        if (function_exists('sendLog')) {
            sendLog("   🖼️  Processing {$mimeType} image... (75%)", 'info');
        }
        
        // Generate filename
        $filename = 'prod_' . $productId . '_' . time() . '.' . $outputExtension;
        $filepath = $uploadDir . $filename;
        
        // Try to achieve target file size with quality adjustment
        $targetSizeKB = round($targetMaxSize / 1024, 0);
        if (function_exists('sendLog')) {
            sendLog("   💾 Optimizing quality (target: {$targetSizeKB}KB)... (80%)", 'info');
        }
        
        $saved = false;
        $finalQuality = $quality;
        $attempts = 0;
        $maxAttempts = 5;
        
        do {
            // Save resized image
            switch ($outputFormat) {
                case 'jpg':
                    $saved = @imagejpeg($resizedImage, $filepath, $finalQuality);
                    break;
                case 'png':
                    // PNG compression level (0-9, higher = more compression)
                    $pngLevel = (int)(9 - ($finalQuality / 10)); // Convert quality to PNG level
                    $saved = @imagepng($resizedImage, $filepath, max(0, min(9, $pngLevel)));
                    break;
                case 'webp':
                    if (function_exists('imagewebp')) {
                        $saved = @imagewebp($resizedImage, $filepath, $finalQuality);
                    }
                    break;
            }
            
            if ($saved) {
                $fileSize = filesize($filepath);
                $currentSizeKB = round($fileSize / 1024, 2);
                $progressPercent = 80 + min(15, ($attempts + 1) * 3); // 80-95%
                
                // If file size is acceptable or we've tried enough, stop
                if ($fileSize <= $targetMaxSize || $finalQuality <= 60 || $attempts >= $maxAttempts) {
                    if (function_exists('sendLog')) {
                        sendLog("   💾 Optimizing quality... ({$progressPercent}%)", 'info');
                    }
                    break;
                }
                
                // Reduce quality for next attempt
                if (function_exists('sendLog') && $attempts < 2) {
                    sendLog("   🔧 Adjusting quality (attempt " . ($attempts + 2) . ")... ({$progressPercent}%)", 'info');
                }
                $finalQuality = max(60, $finalQuality - 10);
                $attempts++;
            } else {
                break;
            }
        } while ($fileSize > $targetMaxSize && $finalQuality > 60);
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        @unlink($tmpFile);
        
        if ($saved) {
            // Get final file size
            $fileSize = filesize($filepath);
            $fileSizeKB = round($fileSize / 1024, 2);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);
            $originalSizeKB = round($originalSize / 1024, 2);
            $originalSizeMB = round($originalSize / 1024 / 1024, 2);
            $savings = round((($originalSize - $fileSize) / $originalSize) * 100, 1);
            
            // Log optimization info
            if (function_exists('sendLog')) {
                $sizeDisplay = $fileSizeMB >= 1 ? "{$fileSizeMB}MB" : "{$fileSizeKB}KB";
                $originalSizeDisplay = $originalSizeMB >= 1 ? "{$originalSizeMB}MB" : "{$originalSizeKB}KB";
                
                if ($originalSizeKB > $fileSizeKB) {
                    sendLog("   ✅ Complete! {$originalSizeDisplay} → {$sizeDisplay} (saved {$savings}%) (100%)", 'success');
                } else {
                    sendLog("   ✅ Complete! Final size: {$sizeDisplay} (100%)", 'success');
                }
            }
            
            return '/uploads/products/' . $filename;
        }
        
        if (function_exists('sendLog')) {
            sendLog("   ❌ Failed to save optimized image", 'error');
        }
        return null;
    } catch (Exception $e) {
        if (function_exists('sendLog')) {
            sendLog("   ❌ Image processing error: " . $e->getMessage(), 'error');
        }
        return null;
    }
}

/**
 * Simple image download without optimization (fallback)
 */
function downloadImageSimple($url, $productId) {
    try {
        if (function_exists('sendLog')) {
            sendLog("   ⬇️  Downloading (no optimization available)...", 'info');
        }
        
        $basePath = dirname(__DIR__, 3);
        $uploadDir = $basePath . '/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = 'jpg';
        }
        
        $filename = 'prod_' . $productId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        $imageData = @file_get_contents($url);
        if ($imageData === false) {
            if (function_exists('sendLog')) {
                sendLog("   ❌ Download failed", 'error');
            }
            return null;
        }
        
        if (file_put_contents($filepath, $imageData) !== false) {
            $fileSize = filesize($filepath);
            $fileSizeKB = round($fileSize / 1024, 2);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);
            $sizeDisplay = $fileSizeMB >= 1 ? "{$fileSizeMB}MB" : "{$fileSizeKB}KB";
            
            if (function_exists('sendLog')) {
                sendLog("   ✅ Downloaded: {$sizeDisplay} (no optimization)", 'success');
            }
            return '/uploads/products/' . $filename;
        }
        
        if (function_exists('sendLog')) {
            sendLog("   ❌ Failed to save file", 'error');
        }
        return null;
    } catch (Exception $e) {
        if (function_exists('sendLog')) {
            sendLog("   ❌ Error: " . $e->getMessage(), 'error');
        }
        return null;
    }
}

