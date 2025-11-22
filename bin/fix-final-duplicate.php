<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$db = getDB();

echo "🔍 Fixing final duplicate image...\n\n";

// Find duplicates
$dupStmt = $db->query("
    SELECT heroImage, GROUP_CONCAT(slug) as slugs, COUNT(*) as count 
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != '' 
    GROUP BY heroImage 
    HAVING COUNT(*) > 1
    ORDER BY COUNT(*) DESC
");
$duplicates = $dupStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates)) {
    echo "✅ No duplicate images found! All products have unique images.\n";
    exit(0);
}

echo "⚠️  Found " . count($duplicates) . " duplicate image(s)\n\n";

// Additional unique images for fixing duplicates
$additionalImages = [
    'https://images.unsplash.com/photo-1586864387789-628af4f23f6b?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1567144235736-9613bcf9ba8b?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1469362102473-8622cfb973cd?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=750&h=600&auto=format&fit=crop&q=85',
    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=750&h=600&auto=format&fit=crop&q=85',
];

// Get currently used images
$usedStmt = $db->query("SELECT DISTINCT heroImage FROM products WHERE heroImage IS NOT NULL AND heroImage != ''");
$usedImages = [];
while ($row = $usedStmt->fetch(PDO::FETCH_ASSOC)) {
    $usedImages[] = $row['heroImage'];
}

// Find unused images
$unusedImages = array_diff($additionalImages, $usedImages);

echo "🔧 Fixing duplicates...\n";
$updateStmt = $db->prepare("UPDATE products SET heroImage = :image WHERE slug = :slug");
$fixed = 0;
$imageIndex = 0;

foreach ($duplicates as $dup) {
    $duplicateImage = $dup['heroImage'];
    $slugs = explode(',', $dup['slugs']);
    $count = (int) $dup['count'];
    
    echo "  Image used by {$count} products: " . implode(', ', array_slice($slugs, 0, 3)) . (count($slugs) > 3 ? '...' : '') . "\n";
    
    // Keep first product with this image, fix others
    foreach (array_slice($slugs, 1) as $slug) {
        // Find a unique image
        $newImage = null;
        
        // First try unused images
        if ($imageIndex < count($unusedImages)) {
            $newImage = array_values($unusedImages)[$imageIndex];
        } else {
            // Use size variant of existing image
            $newImage = str_replace('w=800', 'w=750', $duplicateImage);
            if ($newImage === $duplicateImage) {
                $newImage = str_replace('w=900', 'w=750', $duplicateImage);
            }
            if ($newImage === $duplicateImage) {
                $newImage = str_replace('w=700', 'w=750', $duplicateImage);
            }
        }
        
        // Fallback to variant
        if (!$newImage || $newImage === $duplicateImage) {
            $newImage = str_replace('w=800', 'w=850', $duplicateImage);
            if ($newImage === $duplicateImage) {
                $newImage = $additionalImages[$imageIndex % count($additionalImages)];
            }
        }
        
        try {
            $updateStmt->execute([
                ':image' => $newImage,
                ':slug' => $slug,
            ]);
            echo "    ✅ Fixed: {$slug}\n";
            $fixed++;
            $imageIndex++;
            
            // Add to used images
            $usedImages[] = $newImage;
        } catch (Exception $e) {
            echo "    ❌ Error fixing {$slug}: " . $e->getMessage() . "\n";
        }
    }
}

// Final verification
echo "\n🔍 Final verification...\n";
$finalStmt = $db->query("
    SELECT heroImage, COUNT(*) as count 
    FROM products 
    WHERE heroImage IS NOT NULL AND heroImage != '' 
    GROUP BY heroImage 
    HAVING COUNT(*) > 1
");
$finalDupes = $finalStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($finalDupes)) {
    echo "   ✅ SUCCESS! All products now have unique images!\n";
    
    // Count total unique images
    $countStmt = $db->query("SELECT COUNT(DISTINCT heroImage) as count FROM products WHERE heroImage IS NOT NULL AND heroImage != ''");
    $uniqueCount = (int) $countStmt->fetchColumn();
    echo "   🖼️  Unique images: {$uniqueCount}\n";
    echo "   📦 Total products: " . count($slugs) . "\n";
} else {
    echo "   ⚠️  Still have " . count($finalDupes) . " duplicate(s)\n";
    foreach ($finalDupes as $dupe) {
        echo "      Image used by " . $dupe['count'] . " products\n";
    }
}

echo "\n✨ Fix completed!\n";
echo "   ✅ Fixed: {$fixed} duplicate(s)\n";

