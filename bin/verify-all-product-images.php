<?php
/**
 * Verify All Product Images
 * 
 * Checks which products have images and ensures they load correctly
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.live.php';

$liveConfig = require __DIR__ . '/../config/database.live.php';

$db = new PDO(
    "mysql:host={$liveConfig['host']};dbname={$liveConfig['database']};charset=utf8mb4",
    $liveConfig['username'],
    $liveConfig['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

echo "🔍 Verifying all product images...\n\n";

// Get all products
$allProducts = $db->query("
    SELECT id, name, heroImage, status
    FROM products
    ORDER BY name
")->fetchAll();

$withImages = [];
$withoutImages = [];
$brokenImages = [];
$workingImages = [];

foreach ($allProducts as $product) {
    if (empty($product['heroImage'])) {
        $withoutImages[] = $product;
    } else {
        $withImages[] = $product;
        
        $url = $product['heroImage'];
        
        // Skip external URLs
        if (strpos($url, 'unsplash.com') !== false) {
            continue;
        }
        
        // Test if image is accessible and returns actual image (not HTML)
        if (strpos($url, 's3vgroup.com') !== false) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            // Check if it's actually an image (not HTML loading page)
            $isImage = $httpCode === 200 && strpos($contentType, 'image/') === 0;
            
            // Also check if content starts with image bytes (not HTML)
            $isHtml = strpos($content, '<!DOCTYPE') === 0 || 
                      strpos($content, '<html') === 0 || 
                      strpos($content, 'loading') !== false ||
                      strpos($content, 'page-loader') !== false;
            
            if ($isImage && !$isHtml) {
                $workingImages[] = $product;
            } else {
                $brokenImages[] = [
                    'product' => $product,
                    'url' => $url,
                    'code' => $httpCode,
                    'type' => $contentType,
                    'isHtml' => $isHtml
                ];
            }
        }
    }
}

echo "═══════════════════════════════════════\n";
echo "  PRODUCT IMAGE VERIFICATION\n";
echo "═══════════════════════════════════════\n\n";

echo "📊 Statistics:\n";
echo "  Total Products: " . count($allProducts) . "\n";
echo "  Products WITH images: " . count($withImages) . "\n";
echo "  Products WITHOUT images: " . count($withoutImages) . "\n";
echo "  ✅ Working images: " . count($workingImages) . "\n";
echo "  ❌ Broken images (showing HTML/loading): " . count($brokenImages) . "\n\n";

if (count($withoutImages) > 0) {
    echo "═══════════════════════════════════════\n";
    echo "  PRODUCTS WITHOUT IMAGES\n";
    echo "═══════════════════════════════════════\n\n";
    
    foreach (array_slice($withoutImages, 0, 20) as $product) {
        echo "  - {$product['name']} (Status: {$product['status']})\n";
    }
    
    if (count($withoutImages) > 20) {
        echo "  ... and " . (count($withoutImages) - 20) . " more\n";
    }
    echo "\n";
}

if (count($brokenImages) > 0) {
    echo "═══════════════════════════════════════\n";
    echo "  BROKEN IMAGES (Showing Loading/HTML)\n";
    echo "═══════════════════════════════════════\n\n";
    
    echo "⚠️  These images return HTML pages instead of images!\n";
    echo "   When you visit the URL directly, you see loading animation.\n\n";
    
    foreach ($brokenImages as $item) {
        $product = $item['product'];
        echo "❌ {$product['name']}\n";
        echo "   URL: {$item['url']}\n";
        echo "   HTTP: {$item['code']}\n";
        echo "   Content-Type: {$item['type']}\n";
        if ($item['isHtml']) {
            echo "   ⚠️  Returns HTML (loading page) instead of image!\n";
        }
        echo "\n";
    }
    
    // Check which files exist locally
    echo "📁 Checking local files...\n\n";
    $localDir = __DIR__ . '/../uploads/site';
    $localExists = 0;
    
    foreach (array_slice($brokenImages, 0, 10) as $item) {
        if (preg_match('#/uploads/site/([^/?]+)#', $item['url'], $matches)) {
            $filename = $matches[1];
            $localFile = $localDir . '/' . $filename;
            
            if (file_exists($localFile)) {
                $size = round(filesize($localFile) / 1024 / 1024, 2);
                echo "  ✅ {$filename} EXISTS locally ({$size}MB)\n";
                $localExists++;
            } else {
                echo "  ❌ {$filename} NOT FOUND locally\n";
            }
        }
    }
    
    echo "\n💡 SOLUTION:\n";
    if ($localExists > 0) {
        echo "  These images exist locally but are missing on the server.\n";
        echo "  Upload them to cPanel:\n";
        echo "  1. Go to: https://s3vgroup.com/cpanel/\n";
        echo "  2. File Manager → public_html/uploads/site/\n";
        echo "  3. Upload the missing files\n";
    }
} else {
    echo "✅ All product images are loading correctly!\n";
    echo "   No images are stuck on loading screen.\n";
}

echo "\n";

