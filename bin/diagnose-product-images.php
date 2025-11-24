<?php
/**
 * Diagnose Product Image Display Issues
 * 
 * Checks why product images aren't showing
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

echo "🔍 Diagnosing product image display issues...\n\n";

// Check products with and without images
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$productsWithImages = $db->query("SELECT COUNT(*) FROM products WHERE heroImage IS NOT NULL AND heroImage != ''")->fetchColumn();
$productsWithoutImages = $totalProducts - $productsWithImages;

echo "📊 Product Statistics:\n";
echo "  Total products: $totalProducts\n";
echo "  Products with images: $productsWithImages\n";
echo "  Products without images: $productsWithoutImages\n\n";

// Check image URL patterns
$imageUrls = $db->query("
    SELECT heroImage, COUNT(*) as count
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != ''
    GROUP BY heroImage
    ORDER BY count DESC
    LIMIT 10
")->fetchAll();

echo "📋 Image URL Patterns:\n";
foreach ($imageUrls as $url) {
    $pattern = 'Unknown';
    if (strpos($url['heroImage'], 's3vgroup.com') !== false) {
        $pattern = 's3vgroup.com (local server)';
    } elseif (strpos($url['heroImage'], 'localhost') !== false) {
        $pattern = 'localhost (wrong for live!)';
    } elseif (strpos($url['heroImage'], 'unsplash.com') !== false) {
        $pattern = 'Unsplash (external)';
    } elseif (strpos($url['heroImage'], 'http') === false) {
        $pattern = 'Relative path';
    }
    echo "  {$pattern}: {$url['count']} products\n";
    if ($url['count'] <= 3) {
        echo "    Example: " . substr($url['heroImage'], 0, 80) . "...\n";
    }
}
echo "\n";

// Check for broken image URLs
echo "🔍 Checking sample product images...\n\n";
$sampleProducts = $db->query("
    SELECT id, name, heroImage 
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != ''
    ORDER BY updatedAt DESC
    LIMIT 10
")->fetchAll();

$working = 0;
$broken = 0;
$missing = 0;

foreach ($sampleProducts as $product) {
    $url = $product['heroImage'];
    
    // Skip external URLs
    if (strpos($url, 'unsplash.com') !== false) {
        continue;
    }
    
    // Test if image is accessible
    if (strpos($url, 'http') === 0) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        $isImage = $httpCode === 200 && strpos($contentType, 'image/') === 0;
        
        if ($isImage) {
            $working++;
            echo "✅ {$product['name']}: Image accessible\n";
        } else {
            $broken++;
            echo "❌ {$product['name']}: Image broken (HTTP $httpCode, Type: $contentType)\n";
            echo "   URL: $url\n";
        }
    } else {
        // Relative path - check if file exists locally
        $localPath = __DIR__ . '/../' . ltrim($url, '/');
        if (file_exists($localPath)) {
            $working++;
            echo "✅ {$product['name']}: File exists locally\n";
        } else {
            $missing++;
            echo "❌ {$product['name']}: File missing locally\n";
            echo "   Path: $url\n";
        }
    }
}

echo "\n═══════════════════════════════════════\n";
echo "  DIAGNOSIS SUMMARY\n";
echo "═══════════════════════════════════════\n\n";

if ($broken > 0 || $missing > 0) {
    echo "❌ PROBLEMS FOUND:\n";
    echo "  Broken images: $broken\n";
    echo "  Missing images: $missing\n\n";
    
    echo "💡 Common Issues:\n";
    echo "  1. Images missing on server (upload required)\n";
    echo "  2. Image URLs pointing to wrong domain (localhost vs s3vgroup.com)\n";
    echo "  3. Images returning HTML error pages instead of images\n";
    echo "  4. Relative paths not resolving correctly\n\n";
    
    echo "🔧 Solutions:\n";
    echo "  1. Check: php bin/check-live-website-images.php\n";
    echo "  2. Upload missing images to cPanel\n";
    echo "  3. Fix image URLs in database if pointing to wrong domain\n";
    echo "  4. Ensure images are accessible via HTTP\n";
} else {
    echo "✅ All sample images are accessible!\n";
    echo "   If images still don't display, check:\n";
    echo "   - Browser console for errors\n";
    echo "   - CORS headers\n";
    echo "   - JavaScript blocking image loading\n";
}

