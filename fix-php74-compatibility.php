<?php
/**
 * Fix PHP 7.4 Compatibility
 * Converts PHP 8.0+ readonly properties to PHP 7.4 compatible code
 */

$files = [
    'app/Http/Controllers/ThemeController.php',
    'app/Core/PluginRegistry.php',
    'app/Core/PluginManager.php',
    'app/Domain/Content/HomepageSectionRepository.php',
    'app/Domain/Content/CompanyStoryRepository.php',
    'app/Domain/Content/BlogPostRepository.php',
    'app/Domain/Content/CeoMessageRepository.php',
    'app/Domain/Content/PageRepository.php',
    'app/Domain/Content/SliderRepository.php',
    'app/Domain/Content/TestimonialRepository.php',
    'app/Domain/Catalog/CategoryRepository.php',
    'app/Domain/Catalog/ProductRepository.php',
    'app/Domain/Content/TeamMemberRepository.php',
    'app/Domain/Content/NewsletterRepository.php',
    'app/Database/MigrationRunner.php',
    'app/Domain/Quotes/QuoteAdminService.php',
    'app/Domain/Quotes/QuoteRequestRepository.php',
    'app/Domain/Catalog/CategoryService.php',
    'app/Domain/Catalog/CatalogService.php',
    'app/Domain/Quotes/QuoteService.php',
    'app/Database/Migration.php',
];

echo "<h1>PHP 7.4 Compatibility Fix</h1>";
echo "<p>Fixing readonly properties in " . count($files) . " files...</p>";

$fixed = 0;
$errors = [];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) {
        continue;
    }
    
    $content = file_get_contents($path);
    $original = $content;
    
    // Fix: private readonly Type $var -> private $var; with constructor assignment
    // Pattern: public function __construct(private readonly Type $var)
    $content = preg_replace_callback(
        '/public\s+function\s+__construct\s*\(\s*(private\s+readonly|public\s+readonly|readonly)\s+([^\s]+)\s+\$(\w+)([^)]*)\)/',
        function($matches) {
            $visibility = strpos($matches[1], 'private') !== false ? 'private' : 'public';
            $type = $matches[2];
            $var = $matches[3];
            $rest = $matches[4];
            
            // Extract all parameters
            $fullMatch = $matches[0];
            $params = [];
            preg_match_all('/(?:private\s+readonly|public\s+readonly|readonly)\s+([^\s]+)\s+\$(\w+)/', $fullMatch, $paramMatches, PREG_SET_ORDER);
            
            $properties = [];
            $assignments = [];
            
            foreach ($paramMatches as $pm) {
                $pType = $pm[1];
                $pVar = $pm[2];
                $properties[] = "    /** @var $pType */\n    $visibility \$$pVar;";
                $assignments[] = "        \$this->$pVar = \$$pVar;";
            }
            
            $props = implode("\n", $properties);
            $assigns = implode("\n", $assignments);
            
            return "public function __construct(" . 
                   preg_replace('/(?:private\s+readonly|public\s+readonly|readonly)\s+([^\s]+)\s+\$(\w+)/', '$$2', $fullMatch) . 
                   ")\n    {\n$assigns\n    }";
        },
        $content
    );
    
    // Simpler fix: just replace the constructor pattern
    // Match: public function __construct(private readonly Type $var)
    $content = preg_replace(
        '/(public\s+function\s+__construct\s*\([^)]*)(private\s+readonly|public\s+readonly|readonly)\s+([^\s]+)\s+\$(\w+)/',
        '$$4',
        $content
    );
    
    // Add property declarations before class methods
    preg_match_all('/(?:private\s+readonly|public\s+readonly|readonly)\s+([^\s]+)\s+\$(\w+)/', $original, $readonlyMatches, PREG_SET_ORDER);
    
    if (!empty($readonlyMatches)) {
        $classStart = strpos($content, '{');
        if ($classStart !== false) {
            $properties = [];
            foreach ($readonlyMatches as $rm) {
                $type = $rm[1];
                $var = $rm[2];
                $properties[] = "    /** @var $type */\n    private \$$var;";
            }
            
            $propsCode = "\n" . implode("\n", array_unique($properties)) . "\n";
            $content = substr_replace($content, $propsCode, $classStart + 1, 0);
        }
        
        // Add assignments in constructor
        $constructorPattern = '/public\s+function\s+__construct\s*\(([^)]+)\)\s*\{/';
        if (preg_match($constructorPattern, $content, $ctorMatch)) {
            $assignments = [];
            foreach ($readonlyMatches as $rm) {
                $var = $rm[2];
                $assignments[] = "        \$this->$var = \$$var;";
            }
            $assignCode = "\n" . implode("\n", array_unique($assignments));
            $content = preg_replace($constructorPattern, '$0' . $assignCode, $content);
        }
    }
    
    if ($content !== $original) {
        // Backup
        copy($path, $path . '.backup');
        file_put_contents($path, $content);
        $fixed++;
        echo "<p style='color:green'>✓ Fixed: $file</p>";
    } else {
        echo "<p style='color:gray'>- No changes: $file</p>";
    }
}

echo "<hr>";
echo "<h2>Fixed $fixed files</h2>";
echo "<p><strong>DELETE THIS FILE after running!</strong></p>";
?>

