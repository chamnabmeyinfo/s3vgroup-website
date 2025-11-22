<?php

declare(strict_types=1);

echo "🧹 Starting cleanup operations...\n\n";

$cleanupItems = [];

// 1. Clean up old/unused seed files
$oldFiles = [
    'bin/assign-unique-images-final.php', // Keep only assign-verified-images.php
];

echo "📁 Cleaning up old script files...\n";
foreach ($oldFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        try {
            unlink($fullPath);
            echo "  ✅ Deleted: {$file}\n";
            $cleanupItems[] = $file;
        } catch (Exception $e) {
            echo "  ⚠️  Could not delete {$file}: " . $e->getMessage() . "\n";
        }
    }
}

// 2. Verify and clean up database
require_once __DIR__ . '/../config/database.php';
$db = getDB();

echo "\n🗄️  Cleaning up database...\n";

// Check for products with broken image URLs
$stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE heroImage IS NULL OR heroImage = ''");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$missingImages = (int) $result['count'];

if ($missingImages > 0) {
    echo "  ⚠️  Found {$missingImages} products without images\n";
} else {
    echo "  ✅ All products have images\n";
}

// Check for duplicate images
$dupStmt = $db->query("
    SELECT heroImage, COUNT(*) as count 
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != '' 
    GROUP BY heroImage 
    HAVING COUNT(*) > 1
");
$duplicates = $dupStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates)) {
    echo "  ✅ No duplicate images found\n";
} else {
    echo "  ⚠️  Found " . count($duplicates) . " duplicate image(s)\n";
    echo "     Run: php bin/assign-verified-images.php to fix\n";
}

// Check for products with invalid categories
$invalidCatStmt = $db->query("
    SELECT COUNT(*) as count 
    FROM products p 
    LEFT JOIN categories c ON p.categoryId = c.id 
    WHERE p.categoryId IS NOT NULL AND c.id IS NULL
");
$invalidCats = (int) $invalidCatStmt->fetchColumn();

if ($invalidCats > 0) {
    echo "  ⚠️  Found {$invalidCats} products with invalid categories\n";
} else {
    echo "  ✅ All products have valid categories\n";
}

// 3. Clean up temp/old files
echo "\n📄 Cleaning up temporary files...\n";
$tempPatterns = [
    '*.tmp',
    '*.log',
    '*.bak',
    '*.old',
];

$projectRoot = __DIR__ . '/..';
$cleaned = 0;

foreach ($tempPatterns as $pattern) {
    $files = glob($projectRoot . '/' . $pattern);
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.htaccess') {
            try {
                unlink($file);
                echo "  ✅ Deleted: " . basename($file) . "\n";
                $cleaned++;
            } catch (Exception $e) {
                // Silent fail for temp files
            }
        }
    }
}

if ($cleaned === 0) {
    echo "  ✅ No temporary files found\n";
}

// 4. Verify essential files exist
echo "\n✅ Verifying essential files...\n";
$essentialFiles = [
    'config/database.php',
    'bootstrap/app.php',
    'includes/header.php',
    'includes/footer.php',
    'products.php',
    'index.php',
];

foreach ($essentialFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        echo "  ✅ {$file}\n";
    } else {
        echo "  ⚠️  Missing: {$file}\n";
    }
}

// 5. Clean up orphaned records
echo "\n🔗 Checking for orphaned records...\n";

// Check for orphaned product media
$orphanedMediaStmt = $db->query("
    SELECT COUNT(*) as count 
    FROM product_media pm 
    LEFT JOIN products p ON pm.productId = p.id 
    WHERE p.id IS NULL
");
$orphanedMedia = (int) $orphanedMediaStmt->fetchColumn();

if ($orphanedMedia > 0) {
    echo "  ⚠️  Found {$orphanedMedia} orphaned product media records\n";
    // Optionally delete: $db->exec("DELETE FROM product_media WHERE productId NOT IN (SELECT id FROM products)");
} else {
    echo "  ✅ No orphaned product media found\n";
}

// Summary
echo "\n✨ Cleanup completed!\n";
echo "   📁 Files cleaned: " . count($cleanupItems) . "\n";
echo "   📄 Temp files removed: {$cleaned}\n";
echo "   🗄️  Database status: OK\n";

if (!empty($cleanupItems)) {
    echo "\n📋 Cleaned files:\n";
    foreach ($cleanupItems as $item) {
        echo "   - {$item}\n";
    }
}

echo "\n💡 Next steps:\n";
echo "   1. Run: php bin/assign-verified-images.php (to ensure all images are accessible)\n";
echo "   2. Review products at: http://localhost:8080/products.php\n";
echo "   3. Check admin panel: http://localhost:8080/admin/products.php\n";

