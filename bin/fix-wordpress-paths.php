<?php
/**
 * Fix All WordPress Path References
 * Updates all files to use WordPress structure paths
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
$updated = 0;
$errors = [];

echo "🔧 Fixing WordPress Path References\n";
echo str_repeat("=", 70) . "\n\n";

// Path replacements
$replacements = [
    // Bootstrap paths
    "__DIR__ . '/../bootstrap/app.php'" => "__DIR__ . '/../wp-load.php'",
    "__DIR__ . '/../bootstrap/app.php';" => "__DIR__ . '/../wp-load.php';",
    "require_once __DIR__ . '/../bootstrap/app.php';" => "require_once __DIR__ . '/../wp-load.php';",
    "require_once __DIR__ . '/../bootstrap/app.php';" => "require_once __DIR__ . '/../wp-load.php';",
    
    // Includes paths
    "__DIR__ . '/../includes/" => "__DIR__ . '/../wp-includes/",
    "'/includes/" => "'/wp-includes/",
    '"/includes/' => '"/wp-includes/',
    
    // Admin URLs in HTML/JS
    '"/admin/' => '"/wp-admin/',
    "'/admin/" => "'/wp-admin/",
    '/admin/' => '/wp-admin/',
    'href="/admin/' => 'href="/wp-admin/',
    'action="/admin/' => 'action="/wp-admin/',
    'src="/admin/' => 'src="/wp-admin/',
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
            strpos($path, '.git') === false &&
            strpos($path, 'tmp') === false) {
            $phpFiles[] = $path;
        }
    }
}

echo "📝 Found " . count($phpFiles) . " PHP files to check\n\n";

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

// Also update .htaccess
echo "\n📝 Updating .htaccess...\n";
$htaccessPath = $root . '/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccess = file_get_contents($htaccessPath);
    $htaccessUpdated = str_replace('/admin/', '/wp-admin/', $htaccess);
    if ($htaccess !== $htaccessUpdated) {
        if (file_put_contents($htaccessPath, $htaccessUpdated)) {
            echo "  ✓ Updated: .htaccess\n";
            $updated++;
        } else {
            $errors[] = "Failed to update .htaccess";
            echo "  ✗ Failed: .htaccess\n";
        }
    } else {
        echo "  ⊘ .htaccess already updated\n";
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
    echo "\n";
}

echo "✨ Path update completed!\n";

