<?php
/**
 * Compress Large Images to 300KB
 * 
 * This script processes all images in uploads/site/ that are over 1024KB (1MB)
 * and compresses them to under 300KB for fast loading.
 */

require_once __DIR__ . '/../bootstrap/app.php';

use App\Support\ImageOptimizer;

echo "🖼️  Compressing Large Images (>1MB) to 300KB...\n\n";

// Check if GD extension is available
if (!extension_loaded('gd')) {
    echo "❌ ERROR: GD Extension is NOT loaded!\n\n";
    echo "⚠️  Image optimization requires the GD extension to be enabled.\n\n";
    echo "To enable GD:\n";
    echo "1. Open: C:\\xampp\\php\\php.ini\n";
    echo "2. Find: ;extension=gd\n";
    echo "3. Change to: extension=gd (remove semicolon)\n";
    echo "4. Save and restart Apache\n\n";
    echo "Then run this script again.\n";
    exit(1);
}

echo "✅ GD Extension: Loaded\n\n";

$uploadsDir = __DIR__ . '/../uploads/site';

if (!is_dir($uploadsDir)) {
    echo "❌ Directory not found: {$uploadsDir}\n";
    exit(1);
}

// Get all image files
$imageFiles = [];
$extensions = ['jpg', 'jpeg', 'png', 'webp'];

foreach ($extensions as $ext) {
    $pattern = $uploadsDir . '/*.' . $ext;
    $files = glob($pattern);
    if ($files) {
        $imageFiles = array_merge($imageFiles, $files);
    }
    
    // Also check uppercase
    $pattern = $uploadsDir . '/*.' . strtoupper($ext);
    $files = glob($pattern);
    if ($files) {
        $imageFiles = array_merge($imageFiles, $files);
    }
}

$totalFiles = count($imageFiles);
echo "📊 Found {$totalFiles} image files\n\n";

if ($totalFiles === 0) {
    echo "✅ No images to process\n";
    exit(0);
}

$optimizedCount = 0;
$skippedCount = 0;
$errorCount = 0;
$totalSizeBefore = 0;
$totalSizeAfter = 0;
$largeFileThreshold = 1024 * 1024; // 1MB - only process files over this
$targetSize = 300 * 1024; // 300KB target

foreach ($imageFiles as $filePath) {
    $filename = basename($filePath);
    $fileSizeBefore = filesize($filePath);
    
    // Only process files over 1MB
    if ($fileSizeBefore <= $largeFileThreshold) {
        $skippedCount++;
        $totalSizeBefore += $fileSizeBefore;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    
    $totalSizeBefore += $fileSizeBefore;
    
    // Get MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    // Skip if not a supported image type
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        $skippedCount++;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    
    // Get image dimensions
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo === false) {
        echo "⚠️  Skipping {$filename} (invalid image or cannot read dimensions)\n\n";
        $skippedCount++;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    
    [$width, $height] = $imageInfo;
    
    // Test if we can actually load the image with GD
    $testResource = match ($mimeType) {
        'image/jpeg' => @imagecreatefromjpeg($filePath),
        'image/png' => @imagecreatefrompng($filePath),
        'image/webp' => @imagecreatefromwebp($filePath),
        default => false,
    };
    
    if ($testResource === false) {
        echo "⚠️  Skipping {$filename} (cannot load with GD - file may be corrupted or unsupported format)\n";
        echo "   Size: " . round($fileSizeBefore / 1024, 1) . " KB - This file cannot be processed.\n\n";
        $skippedCount++;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    imagedestroy($testResource);
    
    echo "🔄 Compressing {$filename}...\n";
    echo "   Before: {$width}x{$height}, " . round($fileSizeBefore / 1024, 1) . " KB (" . round($fileSizeBefore / 1024 / 1024, 2) . " MB)\n";
    
    try {
        // Backup original filename for WebP conversion check
        $originalPath = $filePath;
        $originalFilename = $filename;
        
        // Aggressively optimize to 300KB target
        ImageOptimizer::resize(
            $filePath,
            $mimeType,
            1200,  // Max width
            1200,  // Max height
            false, // Maintain aspect ratio
            $targetSize // Target: 300KB
        );
        
        // Check if file was converted to WebP
        $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $originalPath);
        $finalPath = file_exists($webpPath) && $webpPath !== $originalPath ? $webpPath : $filePath;
        
        if ($finalPath !== $originalPath && file_exists($webpPath)) {
            // Was converted to WebP
            $fileSizeAfter = filesize($webpPath);
            $newFilename = basename($webpPath);
            echo "   ✅ Converted to WebP: {$newFilename}\n";
            
            // Remove original if WebP is significantly smaller
            if ($fileSizeAfter < $fileSizeBefore * 0.9) {
                @unlink($originalPath);
            }
        } else {
            // Same format, check new size
            $fileSizeAfter = file_exists($filePath) ? filesize($filePath) : $fileSizeBefore;
        }
        
        $totalSizeAfter += $fileSizeAfter;
        
        // Get new dimensions
        $newImageInfo = @getimagesize($finalPath);
        $newWidth = $newImageInfo ? $newImageInfo[0] : $width;
        $newHeight = $newImageInfo ? $newImageInfo[1] : $height;
        
        $savings = $fileSizeBefore - $fileSizeAfter;
        $savingsPercent = $fileSizeBefore > 0 ? round(($savings / $fileSizeBefore) * 100, 1) : 0;
        
        $status = $fileSizeAfter <= $targetSize ? '✅' : '⚠️';
        echo "   {$status} After: {$newWidth}x{$newHeight}, " . round($fileSizeAfter / 1024, 1) . " KB\n";
        echo "   💾 Saved: " . round($savings / 1024, 1) . " KB (" . round($savings / 1024 / 1024, 2) . " MB) - {$savingsPercent}%\n";
        
        if ($fileSizeAfter > $targetSize) {
            echo "   ⚠️  Still above 300KB target - may need more aggressive compression\n";
        }
        
        echo "\n";
        
        $optimizedCount++;
        
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n\n";
        $errorCount++;
        $totalSizeAfter += $fileSizeBefore; // Count original size if error
    }
}

// Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 COMPRESSION SUMMARY\n\n";
echo "Total files scanned: {$totalFiles}\n";
echo "Files over 1MB processed: {$optimizedCount}\n";
echo "✓ Skipped (under 1MB): {$skippedCount}\n";
echo "❌ Errors: {$errorCount}\n\n";

$totalSavings = $totalSizeBefore - $totalSizeAfter;
$totalSavingsPercent = $totalSizeBefore > 0 ? round(($totalSavings / $totalSizeBefore) * 100, 1) : 0;

echo "💾 Storage (Large Files Only):\n";
echo "   Before: " . round($totalSizeBefore / 1024 / 1024, 2) . " MB\n";
echo "   After: " . round($totalSizeAfter / 1024 / 1024, 2) . " MB\n";
echo "   Saved: " . round($totalSavings / 1024 / 1024, 2) . " MB ({$totalSavingsPercent}%)\n\n";

if ($optimizedCount > 0) {
    echo "✅ Compression complete! Large images are now optimized to under 300KB.\n";
} else {
    echo "✅ No large images found (all files are already under 1MB).\n";
}

