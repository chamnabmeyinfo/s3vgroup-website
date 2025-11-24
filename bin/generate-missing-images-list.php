<?php
/**
 * Generate Complete List of Missing Images
 * 
 * Creates a detailed list of all missing images for upload
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

echo "📋 Generating Missing Images Upload List...\n\n";

$localDir = __DIR__ . '/../uploads/site';
$missing = [];

$products = $db->query("
    SELECT id, name, heroImage 
    FROM products 
    WHERE heroImage LIKE 'https://s3vgroup.com/uploads/site/%'
    ORDER BY name
")->fetchAll();

foreach ($products as $product) {
    $url = $product['heroImage'];
    
    if (preg_match('#/uploads/site/([^/?]+)#', $url, $matches)) {
        $filename = $matches[1];
        
        // Test if accessible
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
        
        if (!$isImage) {
            $localFile = $localDir . '/' . $filename;
            if (file_exists($localFile)) {
                $size = filesize($localFile);
                $sizeMB = round($size / 1024 / 1024, 2);
                $missing[] = [
                    'filename' => $filename,
                    'product' => $product['name'],
                    'size' => $size,
                    'sizeMB' => $sizeMB,
                    'url' => $url,
                    'httpCode' => $httpCode,
                    'contentType' => $contentType
                ];
            }
        }
    }
}

echo "Found " . count($missing) . " missing images\n\n";

if (count($missing) > 0) {
    // Sort by size
    usort($missing, function($a, $b) {
        return $b['size'] - $a['size'];
    });
    
    echo "═══════════════════════════════════════\n";
    echo "  MISSING IMAGES - UPLOAD LIST\n";
    echo "═══════════════════════════════════════\n\n";
    
    $totalSize = 0;
    $num = 1;
    foreach ($missing as $file) {
        echo "{$num}. {$file['filename']} ({$file['sizeMB']}MB)\n";
        echo "   Product: {$file['product']}\n";
        echo "   Local: C:\\xampp\\htdocs\\s3vgroup\\uploads\\site\\{$file['filename']}\n";
        echo "   Remote: public_html/uploads/site/{$file['filename']}\n";
        echo "   Status: HTTP {$file['httpCode']}, Type: {$file['contentType']}\n\n";
        $totalSize += $file['size'];
        $num++;
    }
    
    echo "═══════════════════════════════════════\n";
    echo "  SUMMARY\n";
    echo "═══════════════════════════════════════\n\n";
    echo "Total files: " . count($missing) . "\n";
    echo "Total size: " . round($totalSize / 1024 / 1024, 2) . "MB\n";
    echo "Average size: " . round($totalSize / count($missing) / 1024 / 1024, 2) . "MB per file\n\n";
    
    echo "💡 Upload Instructions:\n";
    echo "   1. Go to: https://s3vgroup.com/cpanel/\n";
    echo "   2. File Manager → public_html/uploads/site/\n";
    echo "   3. Upload all " . count($missing) . " files\n";
    echo "   4. This may take 30-60 minutes due to large file sizes\n\n";
    
    echo "⚠️  Note: These images are very large (50-60MB each).\n";
    echo "   Consider optimizing them first using:\n";
    echo "   php bin/optimize-all-to-1mb.php\n";
}

