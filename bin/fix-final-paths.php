<?php
$files = [
    'wp-admin/migrate-to-folders.php',
    'wp-admin/wordpress-sql-import.php',
    'wp-admin/check-api-files.php',
    'wp-admin/woocommerce-import.php',
    'wp-admin/optional-features.php',
    'wp-admin/faqs.php',
    'wp-admin/reviews.php',
    'wp-admin/seo-tools.php',
    'wp-admin/database-sync.php',
    'wp-admin/sliders.php',
    'wp-admin/company-story.php',
    'wp-admin/team.php',
    'wp-admin/ceo-message.php',
    'wp-admin/page-builder.php',
    'wp-admin/homepage-builder-v2.php',
];

$root = dirname(__DIR__);
$updated = 0;

foreach ($files as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $original = $content;
        
        $content = str_replace("__DIR__ . '/../bootstrap/app.php'", "__DIR__ . '/../wp-load.php'", $content);
        $content = str_replace("__DIR__ . '/../includes/", "__DIR__ . '/../wp-includes/", $content);
        
        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "✓ Fixed: $file\n";
            $updated++;
        }
    }
}

echo "\n✅ Updated $updated files\n";

