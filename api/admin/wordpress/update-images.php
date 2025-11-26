<?php
/**
 * Update WordPress Images API
 * 
 * Scans existing products and downloads/optimizes images from WordPress sites
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

// Set headers for streaming
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header('Connection: keep-alive');

/**
 * Check if an image URL is from WordPress site
 */
function isWordPressImageUrl($url) {
    if (empty($url) || !is_string($url)) {
        return false;
    }
    
    $wordPressDomains = [
        's3vtgroup.com.kh',
        's3vgroup.com',
        'www.s3vtgroup.com.kh',
        'www.s3vgroup.com'
    ];
    
    foreach ($wordPressDomains as $domain) {
        if (strpos($url, $domain) !== false) {
            return true;
        }
    }
    
    return false;
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
    $db = Connection::getInstance();
    
    sendLog('🔍 Scanning products for WordPress image URLs...', 'info');
    sendProgress(5);
    
    // Find all products with WordPress image URLs
    $stmt = $db->query("
        SELECT id, name, heroImage 
        FROM products 
        WHERE heroImage IS NOT NULL 
        AND heroImage != ''
        AND (heroImage LIKE '%s3vtgroup.com.kh%' OR heroImage LIKE '%s3vgroup.com%')
    ");
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalProducts = count($products);
    
    if ($totalProducts == 0) {
        sendLog('✅ No products found with WordPress image URLs!', 'success');
        sendComplete(['updated' => 0, 'skipped' => 0, 'errors' => 0]);
        exit;
    }
    
    sendLog("📊 Found {$totalProducts} products with WordPress image URLs", 'info');
    sendProgress(10);
    
    // Load image functions from import-sql.php
    // We need to extract just the function definitions without executing the main code
    if (!function_exists('downloadImage')) {
        // Read import-sql.php and extract function definitions
        $importSqlFile = __DIR__ . '/import-sql.php';
        if (file_exists($importSqlFile)) {
            // Use output buffering to capture function definitions
            ob_start();
            // Include the file but prevent main execution by checking for a flag
            $GLOBALS['__WP_IMPORT_FUNCTIONS_ONLY'] = true;
            include $importSqlFile;
            unset($GLOBALS['__WP_IMPORT_FUNCTIONS_ONLY']);
            ob_end_clean();
        }
        
        if (!function_exists('downloadImage')) {
            sendLog("❌ downloadImage function not available. Cannot proceed.", 'error');
            sendComplete(['updated' => 0, 'skipped' => 0, 'errors' => 1]);
            exit;
        }
    }
    
    $stats = [
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0
    ];
    
    foreach ($products as $index => $product) {
        $percent = 10 + (($index + 1) / $totalProducts) * 85;
        sendProgress($percent);
        
        try {
            $productId = $product['id'];
            $productName = $product['name'];
            $imageUrl = $product['heroImage'];
            
            // Double-check it's a WordPress URL
            if (!isWordPressImageUrl($imageUrl)) {
                sendLog("⏭️  Skipping: {$productName} (not a WordPress URL)", 'info');
                $stats['skipped']++;
                continue;
            }
            
            sendLog("📥 Processing: {$productName}...", 'info');
            
            // Download and optimize image
            $imagePath = downloadImage($imageUrl, $productId, $productName);
            
            if ($imagePath) {
                // Update product with new image path
                $updateStmt = $db->prepare("
                    UPDATE products 
                    SET heroImage = ?, updatedAt = NOW() 
                    WHERE id = ?
                ");
                $updateStmt->execute([$imagePath, $productId]);
                
                $stats['updated']++;
                sendLog("✅ Updated: {$productName}", 'success');
            } else {
                $stats['errors']++;
                sendLog("❌ Failed to download image for: {$productName}", 'error');
            }
            
        } catch (Exception $e) {
            $stats['errors']++;
            sendLog("❌ Error processing {$product['name']}: " . $e->getMessage(), 'error');
        }
    }
    
    sendProgress(100);
    sendLog("🎉 Update complete!", 'success');
    sendComplete($stats);
    
} catch (Exception $e) {
    sendLog("❌ Fatal error: " . $e->getMessage(), 'error');
    sendComplete(['updated' => 0, 'skipped' => 0, 'errors' => 1]);
}

