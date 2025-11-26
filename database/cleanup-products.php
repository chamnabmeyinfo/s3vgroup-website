<?php
/**
 * Cleanup All Products
 * 
 * Safely removes all products and related data:
 * - All products
 * - All product_media
 * - All product_tags
 * 
 * Categories are preserved.
 * 
 * Run: php database/cleanup-products.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

echo "🧹 Cleaning up all products...\n\n";

try {
    $db = Connection::getInstance();
    
    // Get counts before deletion
    $productCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $mediaCount = $db->query("SELECT COUNT(*) FROM product_media")->fetchColumn();
    $tagsCount = $db->query("SELECT COUNT(*) FROM product_tags")->fetchColumn();
    
    if ($productCount == 0) {
        echo "  ℹ️  No products found. Database is already clean.\n";
        exit(0);
    }
    
    echo "  📊 Current data:\n";
    echo "     - Products: $productCount\n";
    echo "     - Product Media: $mediaCount\n";
    echo "     - Product Tags: $tagsCount\n\n";
    
    echo "  ⚠️  WARNING: This will delete ALL products and related data!\n";
    echo "  📦 Categories will be preserved.\n\n";
    
    // Ask for confirmation (for CLI)
    if (php_sapi_name() === 'cli') {
        echo "  Type 'YES' to confirm: ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if ($line !== 'YES') {
            echo "  ❌ Operation cancelled.\n";
            exit(0);
        }
    }
    
    echo "\n  🗑️  Deleting products and related data...\n";
    
    // Delete in order (respecting foreign keys)
    // 1. Delete product tags (references products)
    $db->exec("DELETE FROM product_tags");
    echo "     ✓ Deleted product tags\n";
    
    // 2. Delete product media (references products)
    $db->exec("DELETE FROM product_media");
    echo "     ✓ Deleted product media\n";
    
    // 3. Delete products
    $db->exec("DELETE FROM products");
    echo "     ✓ Deleted products\n";
    
    // Verify deletion
    $remainingProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $remainingMedia = $db->query("SELECT COUNT(*) FROM product_media")->fetchColumn();
    $remainingTags = $db->query("SELECT COUNT(*) FROM product_tags")->fetchColumn();
    
    if ($remainingProducts == 0 && $remainingMedia == 0 && $remainingTags == 0) {
        echo "\n  ✅ Successfully cleaned up all products!\n";
        echo "     - Removed $productCount products\n";
        echo "     - Removed $mediaCount media files\n";
        echo "     - Removed $tagsCount tags\n";
        echo "     - Categories preserved\n";
    } else {
        echo "\n  ⚠️  Warning: Some data may still exist:\n";
        if ($remainingProducts > 0) echo "     - $remainingProducts products remaining\n";
        if ($remainingMedia > 0) echo "     - $remainingMedia media files remaining\n";
        if ($remainingTags > 0) echo "     - $remainingTags tags remaining\n";
    }
    
    echo "\n✨ Product cleanup complete!\n";
    echo "📝 Ready for WooCommerce import.\n";
    
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

