<?php
/**
 * Update All File Paths to WordPress Structure
 * 
 * This script updates all require/include paths in PHP files
 * to use WordPress directory structure
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
$updated = 0;
$errors = [];

echo "🔄 Updating Paths to WordPress Structure\n";
echo str_repeat("=", 70) . "\n\n";

// Path replacements
$replacements = [
    // Admin paths
    "__DIR__ . '/../admin/" => "__DIR__ . '/../wp-admin/",
    "__DIR__ . '/admin/" => "__DIR__ . '/wp-admin/",
    "'/admin/" => "'/wp-admin/",
    '"/admin/' => '"/wp-admin/',
    "admin/" => "wp-admin/",
    
    // Includes paths
    "__DIR__ . '/../includes/" => "__DIR__ . '/../wp-includes/",
    "__DIR__ . '/includes/" => "__DIR__ . '/wp-includes/",
    "'/includes/" => "'/wp-includes/",
    '"/includes/' => '"/wp-includes/',
    "includes/" => "wp-includes/",
    
    // Uploads paths
    "uploads/" => "wp-content/uploads/",
    "'/uploads/" => "'/wp-content/uploads/",
    '"/uploads/' => '"/wp-content/uploads/',
    
    // Plugins paths
    "plugins/" => "wp-content/plugins/",
    "'/plugins/" => "'/wp-content/plugins/",
    '"/plugins/' => '"/wp-content/plugins/',
    
    // Bootstrap
    "bootstrap/app.php" => "wp-load.php",
    "__DIR__ . '/bootstrap/app.php'" => "__DIR__ . '/wp-load.php'",
    "__DIR__ . '/../bootstrap/app.php'" => "__DIR__ . '/../wp-load.php'",
];

// Get all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        // Skip vendor, node_modules, etc.
        if (strpos($path, 'vendor') === false && 
            strpos($path, 'node_modules') === false &&
            strpos($path, '.git') === false) {
            $phpFiles[] = $path;
        }
    }
}

echo "📝 Found " . count($phpFiles) . " PHP files to update\n\n";

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $original = $content;
    $fileUpdated = false;
    
    foreach ($replacements as $search => $replace) {
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            $fileUpdated = true;
        }
    }
    
    if ($fileUpdated && $content !== $original) {
        if (file_put_contents($file, $content)) {
            $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
            echo "  ✓ Updated: $relative\n";
            $updated++;
        } else {
            $errors[] = "Failed to write: $file";
            echo "  ✗ Failed: $file\n";
        }
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";
echo "✅ Files updated: $updated\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "❌ ERRORS:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n✨ Path update completed!\n";
echo "\n⚠️  Next: Move files to WordPress directories\n";

