<?php
/**
 * Deep Image Loading Analysis
 * 
 * Comprehensive analysis of image loading issues on s3vgroup.com
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

echo "🔍 Deep Image Loading Analysis for s3vgroup.com\n\n";
echo "═══════════════════════════════════════\n";
echo "  COMPREHENSIVE DIAGNOSIS\n";
echo "═══════════════════════════════════════\n\n";

// ============================================
// 1. Get all product images
// ============================================
$products = $db->query("
    SELECT id, name, heroImage, slug, status
    FROM products
    WHERE heroImage IS NOT NULL AND heroImage != ''
    ORDER BY name
")->fetchAll();

echo "📊 Total products with images: " . count($products) . "\n\n";

// ============================================
// 2. Test each image URL thoroughly
// ============================================
$results = [
    'working' => [],
    'missing' => [],
    'html_response' => [],
    'wrong_content_type' => [],
    'timeout' => [],
    'external' => [],
];

foreach ($products as $product) {
    $url = $product['heroImage'];
    
    // Skip external URLs
    if (strpos($url, 'unsplash.com') !== false || strpos($url, 'localhost') !== false) {
        $results['external'][] = $product;
        continue;
    }
    
    // Only check s3vgroup.com images
    if (strpos($url, 's3vgroup.com') === false) {
        continue;
    }
    
    echo "Testing: {$product['name']}\n";
    echo "  URL: {$url}\n";
    
    // Perform comprehensive test
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Extract body from response
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    
    // Analyze response
    $isImage = strpos($contentType, 'image/') === 0;
    $isHtml = strpos($body, '<!DOCTYPE') === 0 || 
              strpos($body, '<html') === 0 || 
              strpos($body, '<body') !== false ||
              strpos($body, 'page-loader') !== false ||
              strpos($body, 'loading') !== false;
    
    echo "  HTTP: {$httpCode}\n";
    echo "  Content-Type: {$contentType}\n";
    echo "  Size: " . ($contentLength ? round($contentLength / 1024, 2) . 'KB' : 'unknown') . "\n";
    echo "  Time: " . round($totalTime, 2) . "s\n";
    
    if ($error) {
        echo "  ❌ Error: {$error}\n";
        $results['timeout'][] = [
            'product' => $product,
            'url' => $url,
            'error' => $error
        ];
    } elseif ($httpCode === 200 && $isImage && !$isHtml) {
        echo "  ✅ Working correctly\n";
        $results['working'][] = $product;
    } elseif ($httpCode === 200 && $isHtml) {
        echo "  ❌ Returns HTML (loading page) instead of image!\n";
        $results['html_response'][] = [
            'product' => $product,
            'url' => $url,
            'content_preview' => substr($body, 0, 200)
        ];
    } elseif ($httpCode === 200 && !$isImage) {
        echo "  ❌ Wrong Content-Type: {$contentType}\n";
        $results['wrong_content_type'][] = [
            'product' => $product,
            'url' => $url,
            'content_type' => $contentType
        ];
    } elseif ($httpCode === 404) {
        echo "  ❌ File not found (404)\n";
        $results['missing'][] = [
            'product' => $product,
            'url' => $url
        ];
    } else {
        echo "  ❌ HTTP {$httpCode}\n";
        $results['missing'][] = [
            'product' => $product,
            'url' => $url,
            'code' => $httpCode
        ];
    }
    echo "\n";
}

// ============================================
// 3. Summary Report
// ============================================
echo "\n═══════════════════════════════════════\n";
echo "  ANALYSIS SUMMARY\n";
echo "═══════════════════════════════════════\n\n";

echo "✅ Working Images: " . count($results['working']) . "\n";
echo "❌ Missing (404): " . count($results['missing']) . "\n";
echo "❌ HTML Response (Loading Page): " . count($results['html_response']) . "\n";
echo "❌ Wrong Content-Type: " . count($results['wrong_content_type']) . "\n";
echo "❌ Timeout/Error: " . count($results['timeout']) . "\n";
echo "🌐 External URLs: " . count($results['external']) . "\n\n";

// ============================================
// 4. Detailed Problem Analysis
// ============================================
if (count($results['html_response']) > 0) {
    echo "═══════════════════════════════════════\n";
    echo "  CRITICAL: Images Returning HTML\n";
    echo "═══════════════════════════════════════\n\n";
    echo "⚠️  These images return HTML pages (loading screen) instead of images!\n";
    echo "   This happens when:\n";
    echo "   1. Image file doesn't exist on server\n";
    echo "   2. .htaccess redirects to index.php\n";
    echo "   3. Server returns 404 page (HTML)\n\n";
    
    foreach (array_slice($results['html_response'], 0, 10) as $item) {
        echo "❌ {$item['product']['name']}\n";
        echo "   URL: {$item['url']}\n";
        echo "   Preview: " . substr($item['content_preview'], 0, 100) . "...\n\n";
    }
    
    // Check if files exist locally
    echo "📁 Checking local files...\n";
    $localDir = __DIR__ . '/../uploads/site';
    $localExists = 0;
    
    foreach (array_slice($results['html_response'], 0, 10) as $item) {
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
    
    if ($localExists > 0) {
        echo "\n💡 SOLUTION:\n";
        echo "   These images exist locally but are missing on the server.\n";
        echo "   Upload them to cPanel:\n";
        echo "   1. Go to: https://s3vgroup.com/cpanel/\n";
        echo "   2. File Manager → public_html/uploads/site/\n";
        echo "   3. Upload the missing files\n";
    }
}

if (count($results['missing']) > 0) {
    echo "\n═══════════════════════════════════════\n";
    echo "  MISSING IMAGES (404)\n";
    echo "═══════════════════════════════════════\n\n";
    
    foreach (array_slice($results['missing'], 0, 10) as $item) {
        echo "❌ {$item['product']['name']}\n";
        echo "   URL: {$item['url']}\n";
        if (isset($item['code'])) {
            echo "   HTTP: {$item['code']}\n";
        }
        echo "\n";
    }
}

// ============================================
// 5. Test Direct Image Access
// ============================================
echo "\n═══════════════════════════════════════\n";
echo "  TESTING DIRECT IMAGE ACCESS\n";
echo "═══════════════════════════════════════\n\n";

// Test a few sample images directly
$sampleImages = array_slice($results['html_response'], 0, 3);
if (empty($sampleImages)) {
    $sampleImages = array_slice($results['missing'], 0, 3);
}

foreach ($sampleImages as $item) {
    $testUrl = $item['url'];
    echo "Testing direct access: {$testUrl}\n";
    
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    
    if (strpos($body, '<!DOCTYPE') === 0 || strpos($body, '<html') === 0) {
        echo "  ❌ Returns HTML page (not image)\n";
        echo "  This confirms the image file is missing on server\n\n";
    } elseif ($httpCode === 200 && strpos($body, "\xFF\xD8") === 0) {
        echo "  ✅ Returns actual JPEG image\n\n";
    } elseif ($httpCode === 200 && strpos($body, "\x89PNG") === 0) {
        echo "  ✅ Returns actual PNG image\n\n";
    } else {
        echo "  ⚠️  HTTP {$httpCode} - Unknown response\n\n";
    }
}

// ============================================
// 6. Recommendations
// ============================================
echo "\n═══════════════════════════════════════\n";
echo "  RECOMMENDATIONS\n";
echo "═══════════════════════════════════════\n\n";

$totalProblems = count($results['html_response']) + count($results['missing']) + count($results['wrong_content_type']);

if ($totalProblems > 0) {
    echo "🔧 Immediate Actions Required:\n\n";
    
    if (count($results['html_response']) > 0) {
        echo "1. Upload Missing Images to Server\n";
        echo "   - " . count($results['html_response']) . " images are returning HTML pages\n";
        echo "   - These files exist locally but not on server\n";
        echo "   - Upload to: public_html/uploads/site/\n\n";
    }
    
    if (count($results['missing']) > 0) {
        echo "2. Fix 404 Errors\n";
        echo "   - " . count($results['missing']) . " images return 404\n";
        echo "   - Check if URLs are correct\n";
        echo "   - Verify files exist on server\n\n";
    }
    
    echo "3. Verify .htaccess Configuration\n";
    echo "   - Ensure images are excluded from rewrite rules\n";
    echo "   - Check that image files are served directly\n\n";
    
    echo "4. Test After Upload\n";
    echo "   - Run: php bin/verify-all-product-images.php\n";
    echo "   - Check browser console for errors\n";
    echo "   - Verify images load correctly\n";
} else {
    echo "✅ All images are working correctly!\n";
    echo "   No issues detected.\n";
}

echo "\n";

