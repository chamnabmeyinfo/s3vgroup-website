<?php
/**
 * Migration script to organize admin pages into feature folders
 * Run once: php admin/migrate-to-folders.php
 */

$migrations = [
    // Catalog
    ['from' => 'admin/products.php', 'to' => 'admin/catalog/products.php'],
    ['from' => 'admin/categories.php', 'to' => 'admin/catalog/categories.php'],
    
    // Content
    ['from' => 'admin/pages.php', 'to' => 'admin/content/pages.php'],
    ['from' => 'admin/team.php', 'to' => 'admin/content/team.php'],
    ['from' => 'admin/testimonials.php', 'to' => 'admin/content/testimonials.php'],
    ['from' => 'admin/sliders.php', 'to' => 'admin/content/sliders.php'],
    ['from' => 'admin/ceo-message.php', 'to' => 'admin/content/ceo-message.php'],
    ['from' => 'admin/company-story.php', 'to' => 'admin/content/company-story.php'],
    ['from' => 'admin/homepage-builder-v2.php', 'to' => 'admin/content/homepage-builder-v2.php'],
    ['from' => 'admin/page-builder.php', 'to' => 'admin/content/page-builder.php'],
    
    // Quotes
    ['from' => 'admin/quotes.php', 'to' => 'admin/quotes/index.php'],
    
    // Settings
    ['from' => 'admin/options.php', 'to' => 'admin/settings/options.php'],
    ['from' => 'admin/media-library.php', 'to' => 'admin/settings/media-library.php'],
    ['from' => 'admin/seo-tools.php', 'to' => 'admin/settings/seo-tools.php'],
    ['from' => 'admin/newsletter.php', 'to' => 'admin/settings/newsletter.php'],
    
    // Auth
    ['from' => 'admin/login.php', 'to' => 'admin/auth/login.php'],
    ['from' => 'admin/logout.php', 'to' => 'admin/auth/logout.php'],
    
    // Utilities
    ['from' => 'admin/database-sync.php', 'to' => 'admin/utilities/database-sync.php'],
    ['from' => 'admin/woocommerce-import.php', 'to' => 'admin/utilities/woocommerce-import.php'],
    ['from' => 'admin/wordpress-sql-import.php', 'to' => 'admin/utilities/wordpress-sql-import.php'],
    ['from' => 'admin/test-db-connection.php', 'to' => 'admin/utilities/test-db-connection.php'],
    ['from' => 'admin/check-api-files.php', 'to' => 'admin/utilities/check-api-files.php'],
    ['from' => 'admin/optional-features.php', 'to' => 'admin/utilities/optional-features.php'],
    ['from' => 'admin/faqs.php', 'to' => 'admin/utilities/faqs.php'],
    ['from' => 'admin/reviews.php', 'to' => 'admin/utilities/reviews.php'],
];

// Create directories
$dirs = ['catalog', 'content', 'quotes', 'settings', 'auth', 'utilities'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "Created directory: $dir/\n";
    }
}

// Move files and update paths
foreach ($migrations as $migration) {
    $from = $migration['from'];
    $to = $migration['to'];
    
    if (!file_exists($from)) {
        echo "Skipping (not found): $from\n";
        continue;
    }
    
    // Read file
    $content = file_get_contents($from);
    
    // Update include paths (from /includes/ to ../includes/)
    $content = preg_replace(
        "/__DIR__ \. '\/includes\/(header|footer)\.php'/",
        "__DIR__ . '/../includes/$1.php'",
        $content
    );
    
    // Update require_once paths for bootstrap, config, includes
    $content = preg_replace(
        "/require_once __DIR__ \. '\/\.\.\/bootstrap\/app\.php';/",
        "require_once __DIR__ . '/../../bootstrap/app.php';",
        $content
    );
    $content = preg_replace(
        "/require_once __DIR__ \. '\/\.\.\/config\/(database|site)\.php';/",
        "require_once __DIR__ . '/../../config/$1.php';",
        $content
    );
    $content = preg_replace(
        "/require_once __DIR__ \. '\/\.\.\/includes\/functions\.php';/",
        "require_once __DIR__ . '/../../includes/functions.php';",
        $content
    );
    
    // Write to new location
    $toDir = dirname($to);
    if (!is_dir($toDir)) {
        mkdir($toDir, 0755, true);
    }
    
    file_put_contents($to, $content);
    echo "Migrated: $from -> $to\n";
    
    // Keep original for now (comment out to delete)
    // unlink($from);
}

echo "\nMigration complete! Review the files and update navigation links.\n";
echo "Original files are still in place. Delete them after testing.\n";

