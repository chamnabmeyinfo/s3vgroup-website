<?php
/**
 * Assign Optimized Images to All Products
 *
 * This script assigns optimized images (WebP preferred) from uploads/site/
 * to every product's heroImage field. Images are cycled through if there
 * are fewer images than products.
 *
 * Usage:
 *   php bin/assign-optimized-product-images.php           # Dry run (preview only)
 *   php bin/assign-optimized-product-images.php --apply   # Apply changes to DB
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';

use App\Database\Connection;

echo "🖼️  Assigning Optimized Images to Products...\n\n";

$args = $argv ?? [];
$apply = in_array('--apply', $args, true);

$uploadsDir = __DIR__ . '/../uploads/site';
if (!is_dir($uploadsDir)) {
    echo "❌ uploads/site directory not found: {$uploadsDir}\n";
    exit(1);
}

// Collect optimized images (WebP preferred)
$optimizedImages = glob($uploadsDir . '/*.webp');

// Fallback to JPG/PNG under 1MB if not enough WebP files
if (empty($optimizedImages)) {
    $fallbackImages = array_merge(
        glob($uploadsDir . '/*.jpg'),
        glob($uploadsDir . '/*.jpeg'),
        glob($uploadsDir . '/*.png')
    );

    foreach ($fallbackImages as $imagePath) {
        if (filesize($imagePath) <= 1024 * 1024) { // under 1MB
            $optimizedImages[] = $imagePath;
        }
    }
}

if (empty($optimizedImages)) {
    echo "❌ No optimized images found in uploads/site/ (WebP or <=1MB JPG/PNG).\n";
    exit(1);
}

sort($optimizedImages, SORT_NATURAL);
$totalImages = count($optimizedImages);

$siteUrl = $siteConfig['url'] ?? 'http://localhost';
$siteUrl = rtrim($siteUrl, '/');

try {
    $pdo = Connection::make();

    $products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $totalProducts = count($products);

    if ($totalProducts === 0) {
        echo "✅ No products found. Nothing to update.\n";
        exit(0);
    }

    echo "📊 Products: {$totalProducts}\n";
    echo "📁 Available optimized images: {$totalImages}\n\n";

    $assignments = [];
    foreach ($products as $index => $product) {
        $imagePath = $optimizedImages[$index % $totalImages];
        $filename = basename($imagePath);
        $url = $siteUrl . '/uploads/site/' . $filename;

        $assignments[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'filename' => $filename,
            'url' => $url,
        ];
    }

    echo "📝 Preview of assignments:\n";
    foreach (array_slice($assignments, 0, 10) as $assignment) {
        echo "   • {$assignment['name']} -> {$assignment['filename']}\n";
    }
    if ($totalProducts > 10) {
        echo "   ... and " . ($totalProducts - 10) . " more\n";
    }
    echo "\n";

    if (!$apply) {
        echo "🔍 Dry run complete. No changes made.\n";
        echo "Run with --apply to update the database:\n";
        echo "  php bin/assign-optimized-product-images.php --apply\n";
        exit(0);
    }

    echo "✅ APPLY MODE - Updating database...\n";

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE products SET heroImage = :url, updatedAt = NOW() WHERE id = :id");

    foreach ($assignments as $assignment) {
        $stmt->execute([
            ':url' => $assignment['url'],
            ':id' => $assignment['id'],
        ]);
    }

    $pdo->commit();

    echo "🎉 Success! Assigned optimized images to {$totalProducts} products.\n";
    echo "Images used (cycled): {$totalImages}\n";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit(1);
}

