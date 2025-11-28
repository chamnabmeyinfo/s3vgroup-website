<?php
/**
 * Complete Ant Elite Rename - Update All References
 * This updates all code to use ae- paths
 * Then you can manually rename directories via File Explorer
 */

$root = dirname(__DIR__);
chdir($root);

echo "🔄 Complete Ant Elite (AE) System Rename\n";
echo str_repeat("=", 70) . "\n\n";

// Step 1: Update all file references
echo "🔧 Step 1: Updating all file references...\n";

$replacements = [
    // File paths
    'wp-load.php' => 'ae-load.php',
    'wp-config.php' => 'ae-config.php',
    
    // Directory paths in strings
    '/wp-admin/' => '/ae-admin/',
    '/wp-includes/' => '/ae-includes/',
    '/wp-content/' => '/ae-content/',
    'wp-admin/' => 'ae-admin/',
    'wp-includes/' => 'ae-includes/',
    'wp-content/' => 'ae-content/',
    
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
];

// Get all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
$excludeDirs = ['vendor', 'node_modules', '.git', 'tmp', 'storage', 'sql'];

foreach ($iterator as $file) {
    if ($file->isFile() && ($file->getExtension() === 'php' || $file->getExtension() === 'js' || $file->getExtension() === 'css')) {
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
echo "\n📝 Step 2: Updating .htaccess...\n";
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

echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";
echo "✅ Files updated: $updated\n\n";

echo "⚠️  MANUAL STEP REQUIRED:\n";
echo "   Please manually rename these directories via File Explorer:\n";
echo "   1. wp-admin → ae-admin\n";
echo "   2. wp-includes → ae-includes\n";
echo "   3. wp-content → ae-content\n";
echo "   4. wp-load.php → ae-load.php\n";
echo "   5. wp-config.php → ae-config.php\n\n";

echo "✨ All code references have been updated!\n";
echo "   After renaming directories, your Ant Elite system will be complete!\n";

