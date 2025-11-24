<?php
/**
 * Optimize Existing Large Images
 * 
 * This script processes all images in uploads/site/ and optimizes them
 * to ensure fast loading times. It will:
 * - Resize images larger than 1200x1200
 * - Compress images to under 1MB
 * - Convert to WebP when beneficial
 */

require_once __DIR__ . '/../bootstrap/app.php';

use App\Support\ImageOptimizer;

echo "🖼️  Optimizing Existing Images in uploads/site/...\n\n";

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
echo "📊 Found {$totalFiles} image files to process\n\n";

if ($totalFiles === 0) {
    echo "✅ No images to optimize\n";
    exit(0);
}

$optimizedCount = 0;
$skippedCount = 0;
$errorCount = 0;
$totalSizeBefore = 0;
$totalSizeAfter = 0;

foreach ($imageFiles as $filePath) {
    $filename = basename($filePath);
    $fileSizeBefore = filesize($filePath);
    $totalSizeBefore += $fileSizeBefore;
    
    // Get MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    // Skip if not a supported image type
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        $skippedCount++;
        continue;
    }
    
    // Get image dimensions
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo === false) {
        echo "⚠️  Skipping {$filename} (invalid image)\n";
        $skippedCount++;
        continue;
    }
    
    [$width, $height] = $imageInfo;
    $needsOptimization = $fileSizeBefore > 1024 * 1024 || // > 1MB
                         $width > 1200 || 
                         $height > 1200;
    
    if (!$needsOptimization) {
        echo "✓ {$filename} - Already optimized ({$width}x{$height}, " . round($fileSizeBefore / 1024, 1) . " KB)\n";
        $skippedCount++;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    
    echo "🔄 Optimizing {$filename}...\n";
    echo "   Before: {$width}x{$height}, " . round($fileSizeBefore / 1024, 1) . " KB\n";
    
    try {
        // Backup original filename for WebP conversion check
        $originalPath = $filePath;
        
        // Optimize the image
        ImageOptimizer::resize(
            $filePath,
            $mimeType,
            1200,  // Max width
            1200,  // Max height
            false, // Maintain aspect ratio
            1024 * 1024 // Target: 1MB
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
        
        echo "   After: {$newWidth}x{$newHeight}, " . round($fileSizeAfter / 1024, 1) . " KB\n";
        echo "   💾 Saved: " . round($savings / 1024, 1) . " KB ({$savingsPercent}%)\n\n";
        
        $optimizedCount++;
        
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n\n";
        $errorCount++;
        $totalSizeAfter += $fileSizeBefore; // Count original size if error
    }
}

// Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 OPTIMIZATION SUMMARY\n\n";
echo "Total files processed: {$totalFiles}\n";
echo "✅ Optimized: {$optimizedCount}\n";
echo "✓ Skipped (already optimized): {$skippedCount}\n";
echo "❌ Errors: {$errorCount}\n\n";

$totalSavings = $totalSizeBefore - $totalSizeAfter;
$totalSavingsPercent = $totalSizeBefore > 0 ? round(($totalSavings / $totalSizeBefore) * 100, 1) : 0;

echo "💾 Storage:\n";
echo "   Before: " . round($totalSizeBefore / 1024 / 1024, 2) . " MB\n";
echo "   After: " . round($totalSizeAfter / 1024 / 1024, 2) . " MB\n";
echo "   Saved: " . round($totalSavings / 1024 / 1024, 2) . " MB ({$totalSavingsPercent}%)\n\n";

if ($optimizedCount > 0) {
    echo "✅ Optimization complete! Images are now optimized for fast loading.\n";
} else {
    echo "✅ All images are already optimized.\n";
}

