<?php
/**
 * Clean up duplicate path segments
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
$stmt = $db->query("SELECT id, heroImage FROM products WHERE heroImage LIKE '%/uploads/uploads/%' OR heroImage LIKE '%/uploads/products/%' OR heroImage LIKE '%/uploads/site/%'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($products) . " products with incorrect paths.\n\n";

$updated = 0;

foreach ($products as $product) {
    $original = $product['heroImage'];
    
    // Fix the path
    $fixed = $original;
    
    // Remove /ae-content/uploads/ prefix temporarily
    $fixed = preg_replace('#^/ae-content/uploads/#', '', $fixed);
    
    // Remove any uploads/ prefix
    $fixed = preg_replace('#^uploads/#', '', $fixed);
    
    // Add back the correct prefix
    $fixed = '/ae-content/uploads/' . $fixed;
    
    if ($fixed !== $original) {
        $stmt = $db->prepare("UPDATE products SET heroImage = ? WHERE id = ?");
        $stmt->execute([$fixed, $product['id']]);
        echo "Fixed {$product['id']}: $original -> $fixed\n";
        $updated++;
    }
}

echo "\nTotal fixed: $updated\n";
