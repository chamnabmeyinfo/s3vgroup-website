<?php
/**
 * Fix All Paths to Use AE (Ant Elite) System
 */

$root = dirname(__DIR__);
chdir($root);

echo "🔧 Fixing all paths to Ant Elite (AE) system...\n\n";

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
    
    // Constants (keep both for compatibility)
    'WPINC' => 'AEINC',
    'WP_CONTENT_DIR' => 'AE_CONTENT_DIR',
    'WP_CONTENT_URL' => 'AE_CONTENT_URL',
    'WP_PLUGIN_DIR' => 'AE_PLUGIN_DIR',
    'WP_PLUGIN_URL' => 'AE_PLUGIN_URL',
    'WP_ADMIN' => 'AE_ADMIN',
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

// Update .htaccess
$htaccessPath = $root . '/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccess = file_get_contents($htaccessPath);
    $htaccessUpdated = str_replace('/wp-admin/', '/ae-admin/', $htaccess);
    if ($htaccess !== $htaccessUpdated) {
        file_put_contents($htaccessPath, $htaccessUpdated);
        echo "  ✓ Updated: .htaccess\n";
        $updated++;
    }
}

echo "\n✅ Updated $updated files\n";

