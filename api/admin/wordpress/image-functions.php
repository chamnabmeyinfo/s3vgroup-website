<?php
/**
 * Shared Image Processing Functions for WordPress Import
 * 
 * This file contains reusable functions for downloading and optimizing images
 */

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

// Note: downloadImage function is too large to include here
// It's defined in import-sql.php and should be loaded from there
// For update-images.php, we'll include import-sql.php functions conditionally

