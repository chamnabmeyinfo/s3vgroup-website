<?php
/**
 * Remove All Product Images
 * 
 * This script removes all images from products:
 * - Clears heroImage field in products table
 * - Deletes all records from product_media table
 * - Optionally deletes image files from uploads/site/
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

echo "🗑️  Removing All Product Images...\n\n";

// Check for confirmation
$args = $argv ?? [];
$confirm = in_array('--confirm', $args, true);
$deleteFiles = in_array('--delete-files', $args, true);

if (!$confirm) {
    echo "⚠️  WARNING: This will remove ALL images from ALL products!\n\n";
    echo "This script will:\n";
    echo "  - Clear heroImage field for all products\n";
    echo "  - Delete all records from product_media table\n";
    if ($deleteFiles) {
        echo "  - Delete image files from uploads/site/ (if not used elsewhere)\n";
    }
    echo "\n";
    echo "To proceed, run with --confirm flag:\n";
    echo "  php bin/remove-all-product-images.php --confirm\n";
    if ($deleteFiles) {
        echo "  php bin/remove-all-product-images.php --confirm --delete-files\n";
    }
    exit(1);
}

try {
    $pdo = Connection::make();
    
    // Get current counts
    $productsCount = $pdo->query("SELECT COUNT(*) FROM products WHERE heroImage IS NOT NULL AND heroImage != ''")->fetchColumn();
    $mediaCount = $pdo->query("SELECT COUNT(*) FROM product_media")->fetchColumn();
    
    echo "📊 Current Status:\n";
    echo "   Products with heroImage: {$productsCount}\n";
    echo "   Product media records: {$mediaCount}\n\n";
    
    if ($productsCount === 0 && $mediaCount === 0) {
        echo "✅ No product images found. Nothing to remove.\n";
        exit(0);
    }
    
    // Collect image paths before deletion (for file deletion)
    $imagePaths = [];
    
    if ($deleteFiles) {
        // Get all heroImage paths
        $heroImages = $pdo->query("SELECT DISTINCT heroImage FROM products WHERE heroImage IS NOT NULL AND heroImage != ''")->fetchAll(PDO::FETCH_COLUMN);
        
        // Get all product_media URLs
        $mediaUrls = $pdo->query("SELECT url FROM product_media")->fetchAll(PDO::FETCH_COLUMN);
        
        $imagePaths = array_merge($heroImages, $mediaUrls);
        
        echo "📁 Found " . count($imagePaths) . " image references\n";
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Clear heroImage for all products
        echo "🔄 Clearing heroImage from products table...\n";
        $stmt = $pdo->prepare("UPDATE products SET heroImage = NULL, updatedAt = NOW() WHERE heroImage IS NOT NULL AND heroImage != ''");
        $stmt->execute();
        $heroCleared = $stmt->rowCount();
        echo "   ✅ Cleared {$heroCleared} product heroImage fields\n";
        
        // Delete all product_media records
        echo "🔄 Deleting product_media records...\n";
        $stmt = $pdo->prepare("DELETE FROM product_media");
        $stmt->execute();
        $mediaDeleted = $stmt->rowCount();
        echo "   ✅ Deleted {$mediaDeleted} product_media records\n";
        
        // Commit transaction
        $pdo->commit();
        
        echo "\n✅ Successfully removed all product images from database!\n\n";
        
        // Delete image files if requested
        if ($deleteFiles && !empty($imagePaths)) {
            echo "🗑️  Deleting image files...\n";
            $uploadsDir = __DIR__ . '/../uploads/site/';
            $deletedCount = 0;
            $notFoundCount = 0;
            $inUseCount = 0;
            
            foreach ($imagePaths as $imagePath) {
                // Extract filename from path (handle both relative and absolute paths)
                $filename = basename($imagePath);
                $filePath = $uploadsDir . $filename;
                
                // Check if file exists
                if (!file_exists($filePath)) {
                    $notFoundCount++;
                    continue;
                }
                
                // Check if file is used elsewhere (in other tables)
                $usedElsewhere = false;
                
                // Check sliders
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM sliders WHERE image = :path OR image LIKE :path2");
                $stmt->execute([':path' => $imagePath, ':path2' => '%' . $filename]);
                if ($stmt->fetchColumn() > 0) {
                    $usedElsewhere = true;
                }
                
                // Check team members
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE photo = :path OR photo LIKE :path2");
                $stmt->execute([':path' => $imagePath, ':path2' => '%' . $filename]);
                if ($stmt->fetchColumn() > 0) {
                    $usedElsewhere = true;
                }
                
                // Check testimonials
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM testimonials WHERE photo = :path OR photo LIKE :path2");
                $stmt->execute([':path' => $imagePath, ':path2' => '%' . $filename]);
                if ($stmt->fetchColumn() > 0) {
                    $usedElsewhere = true;
                }
                
                // Check site options (logo, favicon, etc.)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_options WHERE value = :path OR value LIKE :path2");
                $stmt->execute([':path' => $imagePath, ':path2' => '%' . $filename]);
                if ($stmt->fetchColumn() > 0) {
                    $usedElsewhere = true;
                }
                
                if ($usedElsewhere) {
                    $inUseCount++;
                    echo "   ⚠️  Skipping {$filename} (used elsewhere)\n";
                } else {
                    if (@unlink($filePath)) {
                        $deletedCount++;
                        echo "   ✅ Deleted {$filename}\n";
                    } else {
                        echo "   ⚠️  Failed to delete {$filename}\n";
                    }
                }
            }
            
            echo "\n📊 File Deletion Summary:\n";
            echo "   ✅ Deleted: {$deletedCount}\n";
            echo "   ⚠️  In use elsewhere: {$inUseCount}\n";
            echo "   ❌ Not found: {$notFoundCount}\n";
        }
        
        echo "\n✅ All product images have been removed!\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit(1);
}

