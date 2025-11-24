<?php
/**
 * Optimize All Images to Under 1MB
 * 
 * Compresses and resizes all images to be under 1MB
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

use App\Support\ImageOptimizer;

$uploadDir = __DIR__ . '/../uploads/site';
$maxFileSize = 1 * 1024 * 1024; // 1MB
$targetQuality = 75; // Start with 75% quality
$maxDimensions = 1920; // Max width/height

echo "🔍 Finding and optimizing large images (>1MB)...\n\n";

$images = glob($uploadDir . '/img_*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$largeImages = [];
$totalOriginalSize = 0;

// Find large images
foreach ($images as $img) {
    $size = filesize($img);
    if ($size > $maxFileSize) {
        $largeImages[] = [
            'path' => $img,
            'name' => basename($img),
            'size' => $size,
            'sizeMB' => round($size / 1024 / 1024, 2)
        ];
        $totalOriginalSize += $size;
    }
}

echo "Found " . count($largeImages) . " images over 1MB\n";
echo "Total size: " . round($totalOriginalSize / 1024 / 1024, 2) . "MB\n\n";

if (count($largeImages) === 0) {
    echo "✅ All images are already under 1MB!\n";
    exit(0);
}

// Check if GD is available
if (!extension_loaded('gd')) {
    echo "❌ GD extension is not loaded!\n";
    echo "   Please enable GD in php.ini:\n";
    echo "   1. Open: C:\\xampp\\php\\php.ini\n";
    echo "   2. Find: ;extension=gd\n";
    echo "   3. Change to: extension=gd\n";
    echo "   4. Restart Apache\n\n";
    echo "   Or use external tool like ImageMagick or online compressor.\n";
    exit(1);
}

echo "Starting optimization...\n\n";

$optimized = 0;
$failed = 0;
$skipped = 0;
$totalSaved = 0;

foreach ($largeImages as $item) {
    $path = $item['path'];
    $filename = $item['name'];
    $originalSize = $item['size'];
    
    $mimeType = mime_content_type($path);
    
    // Skip non-image files
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        echo "⏭️  $filename: Skipped (not a supported image type)\n";
        $skipped++;
        continue;
    }
    
    // Get image dimensions
    $info = getimagesize($path);
    if (!$info) {
        echo "❌ $filename: Failed to read image\n";
        $failed++;
        continue;
    }
    
    [$width, $height] = $info;
    
    // Calculate target dimensions to get under 1MB
    // Rough estimate: 1MB = ~1,000,000 bytes
    // For JPEG at 75% quality: ~2-3 bytes per pixel
    // So we need ~300,000-500,000 pixels max
    // That's roughly 550x550 to 700x700 pixels
    
    $targetWidth = min($width, 1200); // Max 1200px width
    $targetHeight = min($height, 1200); // Max 1200px height
    
    // If still too large, reduce further
    if ($originalSize > 5 * 1024 * 1024) { // > 5MB
        $targetWidth = min($width, 800);
        $targetHeight = min($height, 800);
        $targetQuality = 70;
    }
    
    try {
        // Create backup
        $backupPath = $path . '.backup';
        if (!copy($path, $backupPath)) {
            echo "⚠️  $filename: Could not create backup, skipping\n";
            $skipped++;
            continue;
        }
        
        // Optimize using ImageOptimizer
        ImageOptimizer::resize($path, $mimeType, $targetWidth, $targetHeight, $targetQuality);
        
        $newSize = filesize($path);
        $saved = $originalSize - $newSize;
        $savedMB = round($saved / 1024 / 1024, 2);
        $newSizeMB = round($newSize / 1024 / 1024, 2);
        
        // If still over 1MB, reduce quality further
        if ($newSize > $maxFileSize) {
            // Try lower quality
            copy($backupPath, $path); // Restore
            ImageOptimizer::resize($path, $mimeType, $targetWidth, $targetHeight, 60);
            $newSize = filesize($path);
            $saved = $originalSize - $newSize;
            $savedMB = round($saved / 1024 / 1024, 2);
            $newSizeMB = round($newSize / 1024 / 1024, 2);
        }
        
        // If still over 1MB, reduce dimensions
        if ($newSize > $maxFileSize) {
            copy($backupPath, $path); // Restore
            $targetWidth = min($width, 600);
            $targetHeight = min($height, 600);
            ImageOptimizer::resize($path, $mimeType, $targetWidth, $targetHeight, 65);
            $newSize = filesize($path);
            $saved = $originalSize - $newSize;
            $savedMB = round($saved / 1024 / 1024, 2);
            $newSizeMB = round($newSize / 1024 / 1024, 2);
        }
        
        // Remove backup if successful
        if ($newSize <= $maxFileSize) {
            unlink($backupPath);
            echo "✅ $filename: {$item['sizeMB']}MB → {$newSizeMB}MB (saved {$savedMB}MB)\n";
            $optimized++;
            $totalSaved += $saved;
        } else {
            // Restore original if still too large
            copy($backupPath, $path);
            unlink($backupPath);
            echo "⚠️  $filename: Still {$newSizeMB}MB after optimization (too large to compress further)\n";
            $failed++;
        }
        
    } catch (Exception $e) {
        echo "❌ $filename: Error - " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n═══════════════════════════════════════\n";
echo "  OPTIMIZATION SUMMARY\n";
echo "═══════════════════════════════════════\n\n";
echo "✅ Optimized: $optimized\n";
echo "❌ Failed: $failed\n";
echo "⏭️  Skipped: $skipped\n";
echo "💾 Total saved: " . round($totalSaved / 1024 / 1024, 2) . "MB\n\n";

if ($optimized > 0) {
    echo "💡 Next steps:\n";
    echo "   1. Review optimized images\n";
    echo "   2. Commit changes: git add uploads/site/ && git commit -m 'Optimize images to under 1MB'\n";
    echo "   3. Push to GitHub: git push origin main\n";
    echo "   4. On cPanel: Pull updates to deploy optimized images\n";
}

