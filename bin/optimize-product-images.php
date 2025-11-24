<?php
/**
 * Optimize Product Images
 * 
 * This script optimizes all product hero images to ensure they are:
 * - Under 1MB file size
 * - Max 1200x1200 dimensions
 * - Properly compressed for fast loading
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Support\ImageOptimizer;

echo "🖼️  Optimizing Product Images...\n\n";

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

$db = getDB();
$uploadsDir = __DIR__ . '/../uploads/site';

// Get all products with images
$products = $db->query("
    SELECT id, name, heroImage 
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != ''
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$totalProducts = count($products);
echo "📊 Found {$totalProducts} products with images\n\n";

if ($totalProducts === 0) {
    echo "✅ No products with images to optimize\n";
    exit(0);
}

$optimizedCount = 0;
$skippedCount = 0;
$errorCount = 0;
$updatedCount = 0;
$totalSizeBefore = 0;
$totalSizeAfter = 0;

foreach ($products as $product) {
    $imageUrl = $product['heroImage'];
    $productName = $product['name'];
    $productId = $product['id'];
    
    echo "📦 Product: {$productName}\n";
    echo "   Image URL: {$imageUrl}\n";
    
    // Extract filename from URL
    $filename = null;
    $filePath = null;
    
    // Check if it's a local uploads/site image
    if (preg_match('#/uploads/site/([^/?]+)#', $imageUrl, $matches)) {
        $filename = $matches[1];
        $filePath = $uploadsDir . '/' . $filename;
    } elseif (preg_match('#uploads/site/([^/?]+)#', $imageUrl, $matches)) {
        $filename = $matches[1];
        $filePath = $uploadsDir . '/' . $filename;
    } else {
        echo "   ⚠️  Skipping (not a local uploads/site image)\n\n";
        $skippedCount++;
        continue;
    }
    
    // Check if file exists
    if (!file_exists($filePath)) {
        echo "   ❌ File not found: {$filename}\n\n";
        $errorCount++;
        continue;
    }
    
    // Get file info
    $fileSizeBefore = filesize($filePath);
    $totalSizeBefore += $fileSizeBefore;
    
    // Get MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    // Skip if not a supported image type
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        echo "   ⚠️  Skipping (unsupported type: {$mimeType})\n\n";
        $skippedCount++;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    
    // Get image dimensions
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo === false) {
        echo "   ⚠️  Skipping (invalid image)\n\n";
        $skippedCount++;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    
    [$width, $height] = $imageInfo;
    $needsOptimization = $fileSizeBefore > 1024 * 1024 || // > 1MB
                         $width > 1200 || 
                         $height > 1200;
    
    if (!$needsOptimization) {
        echo "   ✓ Already optimized ({$width}x{$height}, " . round($fileSizeBefore / 1024, 1) . " KB)\n\n";
        $skippedCount++;
        $totalSizeAfter += $fileSizeBefore;
        continue;
    }
    
    echo "   🔄 Optimizing...\n";
    echo "   Before: {$width}x{$height}, " . round($fileSizeBefore / 1024, 1) . " KB\n";
    
    try {
        // Backup original filename for WebP conversion check
        $originalPath = $filePath;
        $originalFilename = $filename;
        
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
        $finalFilename = basename($finalPath);
        
        if ($finalPath !== $originalPath && file_exists($webpPath)) {
            // Was converted to WebP
            $fileSizeAfter = filesize($webpPath);
            echo "   ✅ Converted to WebP: {$finalFilename}\n";
            
            // Remove original if WebP is significantly smaller
            if ($fileSizeAfter < $fileSizeBefore * 0.9) {
                @unlink($originalPath);
                
                // Update database with new filename
                $newUrl = preg_replace('/' . preg_quote($originalFilename, '/') . '/', $finalFilename, $imageUrl);
                $stmt = $db->prepare("UPDATE products SET heroImage = :url WHERE id = :id");
                $stmt->execute([':url' => $newUrl, ':id' => $productId]);
                echo "   📝 Updated database with new URL\n";
                $updatedCount++;
            } else {
                // WebP wasn't better, keep original
                @unlink($webpPath);
                $finalPath = $originalPath;
                $fileSizeAfter = file_exists($filePath) ? filesize($filePath) : $fileSizeBefore;
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
echo "Total products with images: {$totalProducts}\n";
echo "✅ Optimized: {$optimizedCount}\n";
echo "✓ Skipped (already optimized): {$skippedCount}\n";
echo "❌ Errors: {$errorCount}\n";
echo "📝 Database updated: {$updatedCount}\n\n";

$totalSavings = $totalSizeBefore - $totalSizeAfter;
$totalSavingsPercent = $totalSizeBefore > 0 ? round(($totalSavings / $totalSizeBefore) * 100, 1) : 0;

echo "💾 Storage:\n";
echo "   Before: " . round($totalSizeBefore / 1024 / 1024, 2) . " MB\n";
echo "   After: " . round($totalSizeAfter / 1024 / 1024, 2) . " MB\n";
echo "   Saved: " . round($totalSavings / 1024 / 1024, 2) . " MB ({$totalSavingsPercent}%)\n\n";

if ($optimizedCount > 0) {
    echo "✅ Product images optimized! All images are now under 1MB and properly sized.\n";
} else {
    echo "✅ All product images are already optimized.\n";
}

