<?php
/**
 * Force Rename to Ant Elite - Comprehensive Approach
 */

$root = dirname(__DIR__);
chdir($root);

echo "🔄 Force Renaming to Ant Elite (AE) System\n";
echo str_repeat("=", 70) . "\n\n";

function copyRecursive($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }
        } else {
            copy($item->getPathname(), $destPath);
        }
    }
    
    return true;
}

function deleteRecursive($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    
    return rmdir($dir);
}

$items = [
    'wp-admin' => 'ae-admin',
    'wp-includes' => 'ae-includes',
    'wp-content' => 'ae-content',
    'wp-load.php' => 'ae-load.php',
    'wp-config.php' => 'ae-config.php',
];

$renamed = [];
$errors = [];

foreach ($items as $old => $new) {
    $oldPath = $root . DIRECTORY_SEPARATOR . $old;
    $newPath = $root . DIRECTORY_SEPARATOR . $new;
    
    if (!file_exists($oldPath)) {
        echo "  ⊘ Not found: $old\n";
        continue;
    }
    
    if (file_exists($newPath)) {
        echo "  ⊘ Already exists: $new (skipping)\n";
        continue;
    }
    
    echo "  Processing: $old → $new\n";
    
    try {
        // Try direct rename first
        if (rename($oldPath, $newPath)) {
            echo "    ✓ Renamed: $old → $new\n";
            $renamed[] = "$old → $new";
        } else {
            // Fallback: copy then delete
            echo "    Rename failed, trying copy method...\n";
            
            if (is_dir($oldPath)) {
                if (copyRecursive($oldPath, $newPath)) {
                    if (deleteRecursive($oldPath)) {
                        echo "    ✓ Copied and deleted: $old → $new\n";
                        $renamed[] = "$old → $new (copied)";
                    } else {
                        echo "    ⚠ Copied but couldn't delete old: $old\n";
                        $renamed[] = "$old → $new (copied, old remains)";
                    }
                } else {
                    echo "    ✗ Failed to copy: $old\n";
                    $errors[] = "Failed to copy: $old";
                }
            } else {
                if (copy($oldPath, $newPath)) {
                    if (unlink($oldPath)) {
                        echo "    ✓ Copied and deleted: $old → $new\n";
                        $renamed[] = "$old → $new (copied)";
                    } else {
                        echo "    ⚠ Copied but couldn't delete old: $old\n";
                        $renamed[] = "$old → $new (copied, old remains)";
                    }
                } else {
                    echo "    ✗ Failed to copy: $old\n";
                    $errors[] = "Failed to copy: $old";
                }
            }
        }
    } catch (Exception $e) {
        echo "    ✗ Error: " . $e->getMessage() . "\n";
        $errors[] = "Error renaming $old: " . $e->getMessage();
    }
}

// Summary
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";
echo "✅ Items processed: " . count($renamed) . "\n";
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

echo "✨ Renaming process completed!\n";

