<?php
/**
 * Fix Remaining Path References
 */

$root = dirname(__DIR__);
$adminDir = $root . '/wp-admin';

echo "🔧 Fixing remaining path references in wp-admin...\n\n";

$files = glob($adminDir . '/*.php');
$updated = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Fix all variations
    $content = preg_replace(
        "/require_once\s+__DIR__\s+\.\s+'\/\.\.\/bootstrap\/app\.php';/",
        "require_once __DIR__ . '/../wp-load.php';",
        $content
    );
    
    $content = preg_replace(
        "/require_once\s+__DIR__\s+\.\s+'\/\.\.\/includes\//",
        "require_once __DIR__ . '/../wp-includes/",
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        $basename = basename($file);
        echo "  ✓ Updated: $basename\n";
        $updated++;
    }
}

echo "\n✅ Updated $updated files\n";

