<?php
/**
 * Rename Directories and Files from wp- to ae-
 */

$root = dirname(__DIR__);
chdir($root);

echo "🔄 Renaming directories and files from wp- to ae-\n";
echo str_repeat("=", 70) . "\n\n";

$renamed = [];
$errors = [];

// Items to rename
$items = [
    'wp-admin' => 'ae-admin',
    'wp-includes' => 'ae-includes',
    'wp-content' => 'ae-content',
    'wp-load.php' => 'ae-load.php',
    'wp-config.php' => 'ae-config.php',
];

foreach ($items as $old => $new) {
    $oldPath = $root . DIRECTORY_SEPARATOR . $old;
    $newPath = $root . DIRECTORY_SEPARATOR . $new;
    
    if (file_exists($oldPath)) {
        // Check if new path already exists
        if (file_exists($newPath)) {
            echo "  ⊘ Already exists: $new (skipping)\n";
            continue;
        }
        
        // Try to rename
        if (rename($oldPath, $newPath)) {
            echo "  ✓ Renamed: $old → $new\n";
            $renamed[] = "$old → $new";
        } else {
            echo "  ✗ Failed: $old\n";
            $errors[] = "Failed to rename: $old";
            
            // Try alternative: copy then delete
            echo "    Trying copy method...\n";
            if (is_dir($oldPath)) {
                if (copyDirectory($oldPath, $newPath)) {
                    deleteDirectory($oldPath);
                    echo "    ✓ Copied and removed: $old → $new\n";
                    $renamed[] = "$old → $new (copied)";
                } else {
                    echo "    ✗ Copy also failed\n";
                }
            } else {
                if (copy($oldPath, $newPath)) {
                    unlink($oldPath);
                    echo "    ✓ Copied and removed: $old → $new\n";
                    $renamed[] = "$old → $new (copied)";
                } else {
                    echo "    ✗ Copy also failed\n";
                }
            }
        }
    } else {
        echo "  ⊘ Not found: $old\n";
    }
}

function copyDirectory($source, $destination) {
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }
        } else {
            copy($item, $destPath);
        }
    }
    
    return true;
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    
    return rmdir($dir);
}

// Summary
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";
echo "✅ Items renamed: " . count($renamed) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (count($renamed) > 0) {
    echo "📦 RENAMED ITEMS:\n";
    foreach ($renamed as $item) {
        echo "  - $item\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERRORS:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

echo "✨ Renaming completed!\n";

