<?php
/**
 * Generate Upload List for Missing Images
 * 
 * Creates a list of all missing image files that need to be uploaded
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

echo "📋 Generating upload list for missing images...\n\n";

$localDir = __DIR__ . '/../uploads/site';
$missingFiles = [];

// Get all products with s3vgroup.com images
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
        
        // Check if accessible
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
                $missingFiles[] = [
                    'filename' => $filename,
                    'product' => $product['name'],
                    'size' => $size,
                    'sizeMB' => $sizeMB,
                    'url' => $url
                ];
            }
        }
    }
}

echo "Found " . count($missingFiles) . " missing files to upload\n\n";

if (count($missingFiles) > 0) {
    // Sort by size (largest first)
    usort($missingFiles, function($a, $b) {
        return $b['size'] - $a['size'];
    });
    
    echo "═══════════════════════════════════════\n";
    echo "  FILES TO UPLOAD\n";
    echo "═══════════════════════════════════════\n\n";
    
    $totalSize = 0;
    foreach ($missingFiles as $file) {
        echo "📁 {$file['filename']} ({$file['sizeMB']}MB)\n";
        echo "   Product: {$file['product']}\n";
        echo "   Local: C:\\xampp\\htdocs\\s3vgroup\\uploads\\site\\{$file['filename']}\n";
        echo "   Remote: public_html/uploads/site/{$file['filename']}\n\n";
        $totalSize += $file['size'];
    }
    
    echo "═══════════════════════════════════════\n";
    echo "  SUMMARY\n";
    echo "═══════════════════════════════════════\n\n";
    echo "Total files: " . count($missingFiles) . "\n";
    echo "Total size: " . round($totalSize / 1024 / 1024, 2) . "MB\n\n";
    
    // Create upload script
    $uploadScript = "#!/bin/bash\n";
    $uploadScript .= "# Upload missing images to cPanel\n";
    $uploadScript .= "# Usage: Upload these files via cPanel File Manager or FTP\n\n";
    $uploadScript .= "FILES=(\n";
    foreach ($missingFiles as $file) {
        $uploadScript .= "    '{$file['filename']}'\n";
    }
    $uploadScript .= ")\n\n";
    $uploadScript .= "echo 'Upload " . count($missingFiles) . " files to: public_html/uploads/site/'\n";
    
    file_put_contents(__DIR__ . '/upload-missing-images.sh', $uploadScript);
    
    // Create Windows batch file
    $batchFile = "@echo off\n";
    $batchFile .= "REM Upload missing images to cPanel\n";
    $batchFile .= "REM Upload these files via cPanel File Manager or FTP\n\n";
    $batchFile .= "echo Upload " . count($missingFiles) . " files to: public_html/uploads/site/\n";
    $batchFile .= "echo.\n";
    $batchFile .= "echo Files to upload:\n";
    foreach ($missingFiles as $file) {
        $batchFile .= "echo   {$file['filename']} ({$file['sizeMB']}MB)\n";
    }
    
    file_put_contents(__DIR__ . '/upload-missing-images.bat', $batchFile);
    
    echo "✅ Created upload scripts:\n";
    echo "   - bin/upload-missing-images.sh\n";
    echo "   - bin/upload-missing-images.bat\n\n";
}

