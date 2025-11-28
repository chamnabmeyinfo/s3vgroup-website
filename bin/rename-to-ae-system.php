<?php
/**
 * Rename WordPress Structure to Ant Elite (AE) System
 * Changes all wp- prefixes to ae- prefixes
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
chdir($root);

echo "🔄 Renaming WordPress Structure to Ant Elite (AE) System\n";
echo str_repeat("=", 70) . "\n\n";

$renamed = [];
$errors = [];

// Step 1: Rename directories
echo "📁 Step 1: Renaming directories...\n";
$dirs = [
    'wp-admin' => 'ae-admin',
    'wp-includes' => 'ae-includes',
    'wp-content' => 'ae-content',
];

foreach ($dirs as $old => $new) {
    $oldPath = $root . '/' . $old;
    $newPath = $root . '/' . $new;
    
    if (is_dir($oldPath)) {
        if (rename($oldPath, $newPath)) {
            echo "  ✓ Renamed: $old/ → $new/\n";
            $renamed[] = "$old/ → $new/";
        } else {
            echo "  ✗ Failed: $old/\n";
            $errors[] = "Failed to rename directory: $old";
        }
    } else {
        echo "  ⊘ Not found: $old/\n";
    }
}

// Step 2: Rename files
echo "\n📄 Step 2: Renaming files...\n";
$files = [
    'wp-load.php' => 'ae-load.php',
    'wp-config.php' => 'ae-config.php',
];

foreach ($files as $old => $new) {
    $oldPath = $root . '/' . $old;
    $newPath = $root . '/' . $new;
    
    if (file_exists($oldPath)) {
        if (rename($oldPath, $newPath)) {
            echo "  ✓ Renamed: $old → $new\n";
            $renamed[] = "$old → $new";
        } else {
            echo "  ✗ Failed: $old\n";
            $errors[] = "Failed to rename file: $old";
        }
    } else {
        echo "  ⊘ Not found: $old\n";
    }
}

// Step 3: Update all file references
echo "\n🔧 Step 3: Updating file references...\n";

$replacements = [
    // Directory paths
    '/wp-admin/' => '/ae-admin/',
    '/wp-includes/' => '/ae-includes/',
    '/wp-content/' => '/ae-content/',
    'wp-admin/' => 'ae-admin/',
    'wp-includes/' => 'ae-includes/',
    'wp-content/' => 'ae-content/',
    
    // File paths
    'wp-load.php' => 'ae-load.php',
    'wp-config.php' => 'ae-config.php',
    
    // Constants
    'WPINC' => 'AEINC',
    'WP_CONTENT_DIR' => 'AE_CONTENT_DIR',
    'WP_CONTENT_URL' => 'AE_CONTENT_URL',
    'WP_PLUGIN_DIR' => 'AE_PLUGIN_DIR',
    'WP_PLUGIN_URL' => 'AE_PLUGIN_URL',
    'WP_ADMIN' => 'AE_ADMIN',
    'WP_DEBUG' => 'AE_DEBUG',
    'WP_DEBUG_LOG' => 'AE_DEBUG_LOG',
    'WP_DEBUG_DISPLAY' => 'AE_DEBUG_DISPLAY',
    'WP_MEMORY_LIMIT' => 'AE_MEMORY_LIMIT',
    'WP_UPLOAD_DIR' => 'AE_UPLOAD_DIR',
    'WP_UPLOAD_URL' => 'AE_UPLOAD_URL',
    'WP_SITEURL' => 'AE_SITEURL',
    'WP_HOME' => 'AE_HOME',
    
    // Comments
    'WordPress' => 'Ant Elite',
    'WordPress-like' => 'Ant Elite',
];

// Get all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
$excludeDirs = ['vendor', 'node_modules', '.git', 'tmp', 'storage', 'sql', 'bin'];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
        
        // Skip excluded directories
        $skip = false;
        foreach ($excludeDirs as $exclude) {
            if (strpos($relative, $exclude) !== false) {
                $skip = true;
                break;
            }
        }
        
        if (!$skip) {
            $phpFiles[] = $path;
        }
    }
}

$updated = 0;
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
        echo "  ✓ Updated: $relative\n";
        $updated++;
    }
}

// Also update .htaccess
echo "\n📝 Step 4: Updating .htaccess...\n";
$htaccessPath = $root . '/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccess = file_get_contents($htaccessPath);
    $htaccessUpdated = str_replace('/wp-admin/', '/ae-admin/', $htaccess);
    if ($htaccess !== $htaccessUpdated) {
        file_put_contents($htaccessPath, $htaccessUpdated);
        echo "  ✓ Updated: .htaccess\n";
    } else {
        echo "  ⊘ .htaccess already updated\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";
echo "📦 Items renamed: " . count($renamed) . "\n";
echo "🔧 Files updated: $updated\n";
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
echo "\n🎉 Your system is now Ant Elite (AE) instead of WordPress (WP)!\n";

