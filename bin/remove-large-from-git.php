<?php
/**
 * Remove Large Images (>1MB) from Git
 * 
 * Removes images over 1MB from Git tracking
 * These will need to be optimized and re-added
 */

require_once __DIR__ . '/../bootstrap/app.php';

$uploadDir = __DIR__ . '/../uploads/site';
$maxSize = 1 * 1024 * 1024; // 1MB

echo "🔍 Finding large images (>1MB) to remove from Git...\n\n";

$images = glob($uploadDir . '/img_*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$largeImages = [];
$totalSize = 0;

foreach ($images as $img) {
    $size = filesize($img);
    if ($size > $maxSize) {
        $largeImages[] = [
            'path' => $img,
            'name' => basename($img),
            'size' => $size,
            'sizeMB' => round($size / 1024 / 1024, 2)
        ];
        $totalSize += $size;
    }
}

echo "Found " . count($largeImages) . " images over 1MB\n";
echo "Total size: " . round($totalSize / 1024 / 1024, 2) . "MB\n\n";

if (count($largeImages) === 0) {
    echo "✅ No large images found!\n";
    exit(0);
}

echo "⚠️  These images will be removed from Git tracking.\n";
echo "   They will remain on your local disk but won't be in the repository.\n\n";

// Remove from Git
$removed = 0;
foreach ($largeImages as $img) {
    $relativePath = 'uploads/site/' . $img['name'];
    
    // Check if file is tracked in Git
    exec("git ls-files --error-unmatch \"$relativePath\" 2>&1", $output, $return);
    
    if ($return === 0) {
        // File is tracked, remove it
        exec("git rm --cached \"$relativePath\" 2>&1", $rmOutput, $rmReturn);
        if ($rmReturn === 0) {
            echo "✅ Removed from Git: {$img['name']} ({$img['sizeMB']}MB)\n";
            $removed++;
        } else {
            echo "⚠️  Failed to remove: {$img['name']}\n";
        }
    } else {
        echo "⏭️  Not in Git: {$img['name']}\n";
    }
}

// Update .gitignore
$gitignorePath = __DIR__ . '/../.gitignore';
$gitignore = file_get_contents($gitignorePath);

if (strpos($gitignore, '# Large images (>1MB)') === false) {
    $gitignore .= "\n# Large images (>1MB) - optimize before adding\n";
    $gitignore .= "uploads/site/*.jpg\n";
    $gitignore .= "uploads/site/*.jpeg\n";
    $gitignore .= "uploads/site/*.png\n";
    $gitignore .= "uploads/site/*.webp\n";
    $gitignore .= "!uploads/site/img_*.jpg\n";
    $gitignore .= "!uploads/site/img_*.jpeg\n";
    $gitignore .= "!uploads/site/img_*.png\n";
    $gitignore .= "!uploads/site/img_*.webp\n";
    
    // Add specific large files
    foreach ($largeImages as $img) {
        $relativePath = 'uploads/site/' . $img['name'];
        if (strpos($gitignore, $relativePath) === false) {
            $gitignore .= $relativePath . "\n";
        }
    }
    
    file_put_contents($gitignorePath, $gitignore);
    echo "\n✅ Updated .gitignore\n";
}

echo "\n📊 Summary:\n";
echo "  Removed from Git: $removed\n";
echo "  Total size removed: " . round($totalSize / 1024 / 1024, 2) . "MB\n\n";

echo "💡 Next steps:\n";
echo "  1. Enable GD extension (see ENABLE-GD-EXTENSION.md)\n";
echo "  2. Run: php bin/optimize-all-to-1mb.php\n";
echo "  3. Add optimized images back to Git\n";
echo "  4. Commit and push changes\n";

