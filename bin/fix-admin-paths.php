<?php
/**
 * Fix all admin files to use ae- paths with wp- fallbacks
 */

$adminDir = __DIR__ . '/../ae-admin';
$files = [
    'ceo-message.php',
    'company-story.php',
    'check-api-files.php',
    'homepage-builder-v2.php',
    'database-sync.php',
    'optional-features.php',
    'page-builder.php',
    'reviews.php',
    'seo-tools.php',
    'woocommerce-import.php',
    'wordpress-sql-import.php',
    'faqs.php',
    'newsletter.php',
    'sliders.php',
    'team.php',
    'testimonials.php',
    'media-library.php',
    'plugins.php',
    'quotes.php',
];

$wpLoadPattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/\.\.\/wp-load\.php[\'"][^;]*;/';
$wpLoadReplacement = "if (file_exists(__DIR__ . '/../ae-load.php')) {\n    require_once __DIR__ . '/../ae-load.php';\n} else {\n    require_once __DIR__ . '/../wp-load.php';\n}";

$wpFunctionsPattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/\.\.\/wp-includes\/functions\.php[\'"][^;]*;/';
$wpFunctionsReplacement = "if (file_exists(__DIR__ . '/../ae-includes/functions.php')) {\n    require_once __DIR__ . '/../ae-includes/functions.php';\n} else {\n    require_once __DIR__ . '/../wp-includes/functions.php';\n}";

foreach ($files as $file) {
    $path = $adminDir . '/' . $file;
    if (!file_exists($path)) {
        continue;
    }
    
    $content = file_get_contents($path);
    $original = $content;
    
    // Replace wp-load.php (even if fallback exists, to ensure consistency)
    $content = preg_replace($wpLoadPattern, $wpLoadReplacement, $content);
    
    // Replace wp-includes/functions.php (only if not already in fallback)
    if (strpos($content, 'if (file_exists(__DIR__ . \'/../ae-includes/functions.php\'))') === false) {
        $content = preg_replace($wpFunctionsPattern, $wpFunctionsReplacement, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Fixed: $file\n";
    }
}

echo "Done!\n";

