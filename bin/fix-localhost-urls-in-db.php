<?php
/**
 * Fix localhost URLs in Database
 * 
 * Replaces localhost URLs with production domain
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

global $siteConfig;
$productionUrl = $siteConfig['url'] ?? 'https://s3vgroup.com';
$productionUrl = rtrim($productionUrl, '/');

echo "🔍 Fixing localhost URLs in live database...\n\n";
echo "Production URL: $productionUrl\n\n";

// Find products with localhost URLs
$products = $db->query("
    SELECT id, name, heroImage 
    FROM products 
    WHERE heroImage LIKE '%localhost%'
")->fetchAll();

echo "Found " . count($products) . " products with localhost URLs\n\n";

if (count($products) === 0) {
    echo "✅ No localhost URLs found!\n";
    exit(0);
}

echo "Products with localhost URLs:\n";
foreach ($products as $product) {
    echo "  - {$product['name']}\n";
    echo "    Old: {$product['heroImage']}\n";
    
    $newUrl = str_replace('http://localhost:8080', $productionUrl, $product['heroImage']);
    $newUrl = str_replace('http://localhost', $productionUrl, $newUrl);
    $newUrl = str_replace('https://localhost:8080', $productionUrl, $newUrl);
    $newUrl = str_replace('https://localhost', $productionUrl, $newUrl);
    
    echo "    New: $newUrl\n\n";
}

echo "⚠️  This will update the live database!\n";
echo "Press Ctrl+C to cancel, or Enter to continue...\n";
// readline(); // Uncomment for interactive mode

$updated = 0;
foreach ($products as $product) {
    $newUrl = str_replace('http://localhost:8080', $productionUrl, $product['heroImage']);
    $newUrl = str_replace('http://localhost', $productionUrl, $newUrl);
    $newUrl = str_replace('https://localhost:8080', $productionUrl, $newUrl);
    $newUrl = str_replace('https://localhost', $productionUrl, $newUrl);
    
    $stmt = $db->prepare("UPDATE products SET heroImage = ? WHERE id = ?");
    $stmt->execute([$newUrl, $product['id']]);
    $updated++;
    
    echo "✅ Updated: {$product['name']}\n";
}

echo "\n📊 Summary:\n";
echo "  Updated: $updated products\n";
echo "  All localhost URLs replaced with: $productionUrl\n";

