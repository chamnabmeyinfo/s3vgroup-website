<?php
/**
 * Fix all frontend files to use Ant Elite paths
 */

$frontendFiles = [
    'products.php',
    'page.php',
    'contact.php',
    'quote.php',
    'about.php',
    'team.php',
    'testimonials.php',
];

$bootstrapPattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/bootstrap\/app\.php[\'"][^;]*;/';
$bootstrapReplacement = "// Load Ant Elite bootstrap (ae-load.php)\nif (file_exists(__DIR__ . '/ae-load.php')) {\n    require_once __DIR__ . '/ae-load.php';\n} else {\n    require_once __DIR__ . '/wp-load.php';\n}";

$functionsPattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/includes\/functions\.php[\'"][^;]*;/';
$functionsReplacement = "// Load functions (check ae-includes first, then wp-includes as fallback)\nif (file_exists(__DIR__ . '/ae-includes/functions.php')) {\n    require_once __DIR__ . '/ae-includes/functions.php';\n} elseif (file_exists(__DIR__ . '/wp-includes/functions.php')) {\n    require_once __DIR__ . '/wp-includes/functions.php';\n} elseif (file_exists(__DIR__ . '/includes/functions.php')) {\n    require_once __DIR__ . '/includes/functions.php';\n}";

$headerPattern = '/include\s+__DIR__\s*\.\s*[\'"]\/includes\/header\.php[\'"][^;]*;/';
$headerReplacement = "// Load header (check ae-includes first, then wp-includes as fallback)\nif (file_exists(__DIR__ . '/ae-includes/header.php')) {\n    include __DIR__ . '/ae-includes/header.php';\n} elseif (file_exists(__DIR__ . '/wp-includes/header.php')) {\n    include __DIR__ . '/wp-includes/header.php';\n} elseif (file_exists(__DIR__ . '/includes/header.php')) {\n    include __DIR__ . '/includes/header.php';\n}";

$footerPattern = '/include\s+__DIR__\s*\.\s*[\'"]\/includes\/footer\.php[\'"][^;]*;/';
$footerReplacement = "// Load footer (check ae-includes first, then wp-includes as fallback)\nif (file_exists(__DIR__ . '/ae-includes/footer.php')) {\n    include __DIR__ . '/ae-includes/footer.php';\n} elseif (file_exists(__DIR__ . '/wp-includes/footer.php')) {\n    include __DIR__ . '/wp-includes/footer.php';\n} elseif (file_exists(__DIR__ . '/includes/footer.php')) {\n    include __DIR__ . '/includes/footer.php';\n}";

foreach ($frontendFiles as $file) {
    $path = __DIR__ . '/../' . $file;
    if (!file_exists($path)) {
        echo "Skipping: $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($path);
    $original = $content;
    
    // Skip if already has fallback logic
    if (strpos($content, 'if (file_exists(__DIR__ . \'/ae-load.php\'))') !== false) {
        echo "Skipping: $file (already fixed)\n";
        continue;
    }
    
    // Replace bootstrap/app.php
    $content = preg_replace($bootstrapPattern, $bootstrapReplacement, $content);
    
    // Replace includes/functions.php
    if (strpos($content, 'if (file_exists(__DIR__ . \'/ae-includes/functions.php\'))') === false) {
        $content = preg_replace($functionsPattern, $functionsReplacement, $content);
    }
    
    // Replace includes/header.php
    if (strpos($content, 'if (file_exists(__DIR__ . \'/ae-includes/header.php\'))') === false) {
        $content = preg_replace($headerPattern, $headerReplacement, $content);
    }
    
    // Replace includes/footer.php
    if (strpos($content, 'if (file_exists(__DIR__ . \'/ae-includes/footer.php\'))') === false) {
        $content = preg_replace($footerPattern, $footerReplacement, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Fixed: $file\n";
    } else {
        echo "No changes needed: $file\n";
    }
}

echo "Done!\n";

