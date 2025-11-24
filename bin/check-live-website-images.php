<?php
/**
 * Check Live Website for Image Loading Errors
 * 
 * Tests the actual website and identifies image loading issues
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

echo "🔍 Checking s3vgroup.com for image loading errors...\n\n";

// Get all products with images
$products = $db->query("
    SELECT id, name, heroImage, slug
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != ''
    ORDER BY name
")->fetchAll();

echo "📊 Found " . count($products) . " products with images\n\n";

$working = [];
$broken = [];
$external = [];
$missing = [];

foreach ($products as $product) {
    $url = $product['heroImage'];
    
    // Skip external images (Unsplash, etc.)
    if (strpos($url, 'unsplash.com') !== false || 
        strpos($url, 'images.unsplash.com') !== false) {
        $external[] = $product;
        continue;
    }
    
    // Only check s3vgroup.com images
    if (strpos($url, 's3vgroup.com') === false) {
        continue;
    }
    
    // Extract filename
    $filename = basename(parse_url($url, PHP_URL_PATH));
    
    // Test image accessibility
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);
    
    $isImage = $httpCode === 200 && strpos($contentType, 'image/') === 0;
    
    if ($isImage) {
        $working[] = [
            'product' => $product['name'],
            'url' => $url,
            'size' => $contentLength ? round($contentLength / 1024 / 1024, 2) . 'MB' : 'unknown'
        ];
    } else {
        $broken[] = [
            'product' => $product['name'],
            'filename' => $filename,
            'url' => $url,
            'code' => $httpCode,
            'type' => $contentType,
            'slug' => $product['slug']
        ];
        
        // Check if file exists locally
        $localFile = __DIR__ . '/../uploads/site/' . $filename;
        if (file_exists($localFile)) {
            $missing[] = [
                'product' => $product['name'],
                'filename' => $filename,
                'url' => $url,
                'localSize' => round(filesize($localFile) / 1024 / 1024, 2) . 'MB',
                'slug' => $product['slug']
            ];
        }
    }
}

echo "═══════════════════════════════════════\n";
echo "  IMAGE LOADING REPORT\n";
echo "═══════════════════════════════════════\n\n";

echo "✅ Working Images: " . count($working) . "\n";
echo "❌ Broken Images: " . count($broken) . "\n";
echo "🌐 External Images (Unsplash): " . count($external) . "\n";
echo "📁 Missing on Server (exist locally): " . count($missing) . "\n\n";

if (count($broken) > 0) {
    echo "═══════════════════════════════════════\n";
    echo "  BROKEN IMAGES DETAILS\n";
    echo "═══════════════════════════════════════\n\n";
    
    foreach (array_slice($broken, 0, 20) as $item) {
        echo "❌ {$item['product']}\n";
        echo "   File: {$item['filename']}\n";
        echo "   URL: {$item['url']}\n";
        echo "   HTTP: {$item['code']}\n";
        if ($item['type']) {
            echo "   Content-Type: {$item['type']}\n";
        }
        echo "\n";
    }
    
    if (count($broken) > 20) {
        echo "   ... and " . (count($broken) - 20) . " more broken images\n\n";
    }
}

if (count($missing) > 0) {
    echo "═══════════════════════════════════════\n";
    echo "  MISSING IMAGES (Need Upload)\n";
    echo "═══════════════════════════════════════\n\n";
    
    $totalSize = 0;
    foreach (array_slice($missing, 0, 15) as $item) {
        $size = (float)str_replace('MB', '', $item['localSize']);
        $totalSize += $size;
        echo "📁 {$item['filename']} ({$item['localSize']})\n";
        echo "   Product: {$item['product']}\n";
        echo "   URL: {$item['url']}\n\n";
    }
    
    if (count($missing) > 15) {
        echo "   ... and " . (count($missing) - 15) . " more files\n\n";
    }
    
    echo "📊 Total size to upload: " . round($totalSize, 2) . "MB\n\n";
    
    echo "💡 SOLUTION:\n";
    echo "   1. Go to: https://s3vgroup.com/cpanel/\n";
    echo "   2. Open File Manager\n";
    echo "   3. Navigate to: public_html/uploads/site/\n";
    echo "   4. Upload the missing files from:\n";
    echo "      C:\\xampp\\htdocs\\s3vgroup\\uploads\\site\\\n\n";
}

// Test actual website page
echo "═══════════════════════════════════════\n";
echo "  TESTING WEBSITE PAGE\n";
echo "═══════════════════════════════════════\n\n";

$pageUrl = 'https://s3vgroup.com/products.php';
$ch = curl_init($pageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$html = curl_exec($ch);
$pageCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($pageCode === 200) {
    echo "✅ Products page loads successfully\n";
    
    // Count image tags in HTML
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
    $imageUrls = $matches[1] ?? [];
    
    echo "   Found " . count($imageUrls) . " image tags in HTML\n";
    
    // Check for broken image patterns
    $brokenPatterns = [
        'placeholder',
        'data:image',
        'svg.*viewBox',
    ];
    
    $hasPlaceholders = false;
    foreach ($imageUrls as $imgUrl) {
        foreach ($brokenPatterns as $pattern) {
            if (preg_match("/$pattern/i", $imgUrl)) {
                $hasPlaceholders = true;
                break 2;
            }
        }
    }
    
    if ($hasPlaceholders) {
        echo "   ⚠️  Some images may be placeholders\n";
    }
} else {
    echo "❌ Products page returned HTTP $pageCode\n";
}

echo "\n";

// Summary
echo "═══════════════════════════════════════\n";
echo "  SUMMARY\n";
echo "═══════════════════════════════════════\n\n";

if (count($broken) === 0 && count($missing) === 0) {
    echo "✅ All images are working correctly!\n";
} else {
    echo "❌ Found " . count($broken) . " broken images\n";
    if (count($missing) > 0) {
        echo "📁 " . count($missing) . " images need to be uploaded to server\n";
    }
    echo "\n";
    echo "🔧 Action Required:\n";
    echo "   Upload missing images to cPanel File Manager\n";
    echo "   Location: public_html/uploads/site/\n";
}

