<?php
/**
 * Download Missing Images Script
 * 
 * Scans the database for image URLs, checks if they exist locally,
 * and if not, attempts to download them from s3vtgroup.com.kh
 */

// Define paths
define('AEPATH', dirname(__DIR__) . '/');
define('AE_CONTENT_DIR', AEPATH . 'ae-content');

// Direct database connection to avoid dependency issues
try {
    $db = new PDO("mysql:host=localhost;dbname=s3vgroup_local;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Remote base URL
$remoteBaseUrl = 'https://s3vtgroup.com.kh/ae-content/uploads/';
// Also check old WP path
$remoteWpUrl = 'https://s3vtgroup.com.kh/wp-content/uploads/';

// Counters
$found = 0;
$downloaded = 0;
$failed = 0;
$skipped = 0;

// 1. Get all products
echo "Scanning products...\n";
$stmt = $db->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 1. Get all products (Loop moved to end for DB updates)

echo "\nSummary:\n";
echo "Found: $found images in database\n";
echo "Downloaded: $downloaded\n";
echo "Skipped (already exists): $skipped\n";
echo "Failed: $failed\n";

function processImage($url) {
    global $found, $downloaded, $failed, $skipped, $remoteBaseUrl, $remoteWpUrl;
    
    if (empty($url)) return;
    
    // Handle comma-separated lists (just in case)
    if (str_contains($url, ',')) {
        $parts = explode(',', $url);
        $url = trim($parts[0]);
    }
    
    $found++;
    
    // Clean URL
    $cleanUrl = $url;
    
    // Remove domain if present
    $cleanUrl = preg_replace('/^https?:\/\/[^\/]+/', '', $cleanUrl);
    
    // Normalize path
    $cleanUrl = str_replace(['/wp-content/uploads/', '/ae-content/uploads/', 'wp-content/uploads/', 'ae-content/uploads/'], '', $cleanUrl);
    $cleanUrl = ltrim($cleanUrl, '/');
    
    // Skip if empty after cleaning
    if (empty($cleanUrl)) return;
    
    // Local path
    $localPath = AE_CONTENT_DIR . '/uploads/' . $cleanUrl;
    
    // Check if exists
    if (file_exists($localPath)) {
        $skipped++;
        return $cleanUrl;
    }
    
    // Create directory if not exists
    $dir = dirname($localPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    echo "Downloading: $cleanUrl... ";
    
    // Try download from AE path
    $remoteUrl = $remoteBaseUrl . $cleanUrl;
    $content = @file_get_contents($remoteUrl);
    
    // Try WP path if failed
    if ($content === false) {
        $remoteUrl = $remoteWpUrl . $cleanUrl;
        $content = @file_get_contents($remoteUrl);
    }
    
    // Try original URL if it was absolute
    if ($content === false && preg_match('/^https?:\/\//', $url)) {
        $content = @file_get_contents($url);
    }
    
    if ($content !== false) {
        if (file_put_contents($localPath, $content)) {
            echo "OK\n";
            $downloaded++;
            return $cleanUrl; // Return the relative path
        } else {
            echo "Write Failed\n";
            $failed++;
        }
    } else {
        echo "Not Found (Remote)\n";
        $failed++;
    }
    return false;
}

// Update DB function
function updateProductImage($db, $id, $column, $newValue) {
    $stmt = $db->prepare("UPDATE products SET $column = ? WHERE id = ?");
    $stmt->execute([$newValue, $id]);
}

// Main Loop Modification
foreach ($products as $product) {
    $updates = [];
    
    // Process hero image
    if (!empty($product['heroImage'])) {
        $newPath = processImage($product['heroImage']);
        if ($newPath && $newPath !== $product['heroImage']) {
            // Only update if it changed (e.g. was absolute, now relative)
            // But wait, processImage returns the clean relative path.
            // If the DB had absolute, we want to save relative.
            if (str_contains($product['heroImage'], 'http')) {
                 $updates['heroImage'] = '/ae-content/uploads/' . $newPath;
            }
        }
    }
    
    // Process gallery images
    if (isset($product['galleryImages']) && !empty($product['galleryImages'])) {
        $images = explode(',', $product['galleryImages']);
        $newImages = [];
        $changed = false;
        
        foreach ($images as $img) {
            $img = trim($img);
            $newPath = processImage($img);
            if ($newPath) {
                $newImages[] = '/ae-content/uploads/' . $newPath;
                if (str_contains($img, 'http')) $changed = true;
            } else {
                // Keep original if failed
                $newImages[] = $img;
            }
        }
        
        if ($changed) {
            $updates['galleryImages'] = implode(',', $newImages);
        }
    }
    
    // Apply updates
    foreach ($updates as $col => $val) {
        updateProductImage($db, $product['id'], $col, $val);
        echo "Updated Product {$product['id']} [$col]\n";
    }
}
