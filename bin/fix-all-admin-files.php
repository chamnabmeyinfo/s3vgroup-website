<?php
/**
 * Fix ALL Admin Files - Final Comprehensive Fix
 */

$root = dirname(__DIR__);
$adminDir = $root . '/wp-admin';

echo "🔧 Fixing ALL admin files...\n\n";

// Get all PHP files recursively
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($adminDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$updated = 0;
foreach ($files as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    
    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $original = $content;
    
    // Replace bootstrap/app.php with wp-load.php
    $content = str_replace(
        "__DIR__ . '/../bootstrap/app.php'",
        "__DIR__ . '/../wp-load.php'",
        $content
    );
    $content = str_replace(
        "__DIR__ . '/../bootstrap/app.php';",
        "__DIR__ . '/../wp-load.php';",
        $content
    );
    
    // Replace includes/ with wp-includes/
    $content = str_replace(
        "__DIR__ . '/../includes/",
        "__DIR__ . '/../wp-includes/",
        $content
    );
    $content = str_replace(
        "'/../includes/",
        "'/../wp-includes/",
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $filePath);
        echo "  ✓ Updated: $relative\n";
        $updated++;
    }
}

echo "\n✅ Updated $updated files\n";

