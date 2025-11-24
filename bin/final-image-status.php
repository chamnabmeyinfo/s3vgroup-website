<?php
/**
 * Final Image Status Check
 * 
 * Quick check of all image issues
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

echo "🔍 Final Image Status Check\n\n";

$products = $db->query("
    SELECT name, heroImage 
    FROM products 
    WHERE heroImage LIKE 'https://s3vgroup.com/uploads/site/%'
    ORDER BY name
")->fetchAll();

$htmlResponse = [];
$working = [];
$missing = [];

foreach ($products as $product) {
    $url = $product['heroImage'];
    
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
    
    if ($httpCode === 200 && strpos($contentType, 'text/html') === 0) {
        $htmlResponse[] = $product;
    } elseif ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
        $working[] = $product;
    } else {
        $missing[] = $product;
    }
}

echo "📊 Summary:\n";
echo "  ✅ Working: " . count($working) . "\n";
echo "  ❌ HTML Response (Loading Screen): " . count($htmlResponse) . "\n";
echo "  ❌ Missing (404): " . count($missing) . "\n\n";

if (count($htmlResponse) > 0) {
    echo "❌ Images Returning HTML (Loading Screen):\n";
    foreach (array_slice($htmlResponse, 0, 10) as $p) {
        echo "  - {$p['name']}\n";
    }
    if (count($htmlResponse) > 10) {
        echo "  ... and " . (count($htmlResponse) - 10) . " more\n";
    }
    echo "\n💡 These images need to be uploaded to server.\n";
}

