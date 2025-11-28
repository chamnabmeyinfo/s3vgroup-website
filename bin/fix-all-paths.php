<?php
/**
 * Fix ALL Path References - Comprehensive Update
 */

$root = dirname(__DIR__);
chdir($root);

echo "🔧 Fixing ALL path references...\n\n";

$replacements = [
    // Bootstrap paths
    "/../bootstrap/app.php" => "/../wp-load.php",
    "/bootstrap/app.php" => "/wp-load.php",
    "bootstrap/app.php" => "wp-load.php",
    
    // Includes paths
    "/../includes/" => "/../wp-includes/",
    "/includes/" => "/wp-includes/",
    "'/includes/" => "'/wp-includes/",
    '"/includes/' => '"/wp-includes/',
];

// Get all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
$excludeDirs = ['vendor', 'node_modules', '.git', 'tmp', 'storage', 'sql'];

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

echo "📝 Found " . count($phpFiles) . " PHP files to check\n\n";

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

echo "\n✅ Updated $updated files\n";

