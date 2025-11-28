<?php
/**
 * Fix Image Paths in Database
 * Adds /ae-content/ prefix to paths that are missing it
 */

// Connect to database
try {
    $db = new PDO("mysql:host=localhost;dbname=s3vgroup_local;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Get all products
$stmt = $db->query("SELECT id, heroImage FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;

foreach ($products as $product) {
    // Fix heroImage
    if (!empty($product['heroImage'])) {
        $fixed = fixPath($product['heroImage']);
        if ($fixed !== $product['heroImage']) {
            $stmt = $db->prepare("UPDATE products SET heroImage = ? WHERE id = ?");
            $stmt->execute([$fixed, $product['id']]);
            echo "Updated {$product['id']}: {$product['heroImage']} -> $fixed\n";
            $updated++;
        }
    }
}

echo "\nTotal updates: $updated\n";

function fixPath($path) {
    if (empty($path)) return $path;
    
    // Already has ae-content or is external URL
    if (str_contains($path, 'ae-content') || str_contains($path, 'http')) {
        return $path;
    }
    
    // Remove any existing prefixes
    $path = preg_replace('#^/?(uploads/|wp-content/uploads/|ae-content/uploads/)#', '', $path);
    
    // Add ae-content/uploads prefix
    return '/ae-content/uploads/' . $path;
}
