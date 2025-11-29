<?php
/**
 * Check Production Code Status
 * This will show what code is actually on production
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Production Code Check</h1>";

// Check PHP version
echo "<h2>PHP Version</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Check critical files
echo "<h2>Critical Files Check</h2>";

$files = [
    'app/Domain/Settings/SiteOptionRepository.php' => 'SiteOptionRepository',
    'app/Domain/Settings/SiteOptionService.php' => 'SiteOptionService',
    'app/Domain/Theme/ThemeRepository.php' => 'ThemeRepository',
    'app/Domain/Catalog/ProductRepository.php' => 'ProductRepository',
];

foreach ($files as $file => $name) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Check for readonly
        if (preg_match('/private\s+readonly|public\s+readonly|readonly\s+private|readonly\s+public/', $content)) {
            echo "<p style='color:red'>✗ $name - STILL HAS readonly properties (OLD CODE)</p>";
            
            // Show the problematic line
            $lines = explode("\n", $content);
            foreach ($lines as $num => $line) {
                if (preg_match('/readonly/', $line)) {
                    echo "<pre style='background:#fee;padding:5px;'>Line " . ($num + 1) . ": " . htmlspecialchars($line) . "</pre>";
                }
            }
        } else {
            echo "<p style='color:green'>✓ $name - No readonly properties (FIXED CODE)</p>";
        }
        
        // Check for match expressions
        if (preg_match('/\bmatch\s*\(/', $content)) {
            echo "<p style='color:orange'>⚠ $name - Has match() expressions (PHP 8.0+)</p>";
        }
    } else {
        echo "<p style='color:red'>✗ $name - File not found!</p>";
    }
}

// Check git status
echo "<h2>Git Status</h2>";
if (function_exists('exec')) {
    $gitStatus = [];
    exec('cd ' . escapeshellarg(__DIR__) . ' && git status 2>&1', $gitStatus);
    echo "<pre>" . implode("\n", $gitStatus) . "</pre>";
    
    $gitLog = [];
    exec('cd ' . escapeshellarg(__DIR__) . ' && git log --oneline -3 2>&1', $gitLog);
    echo "<h3>Recent Commits:</h3>";
    echo "<pre>" . implode("\n", $gitLog) . "</pre>";
    
    $gitRemote = [];
    exec('cd ' . escapeshellarg(__DIR__) . ' && git remote -v 2>&1', $gitRemote);
    echo "<h3>Git Remote:</h3>";
    echo "<pre>" . implode("\n", $gitRemote) . "</pre>";
} else {
    echo "<p>Cannot check git (exec disabled)</p>";
}

// Check if files are different from GitHub
echo "<h2>File Content Sample</h2>";
$sampleFile = __DIR__ . '/app/Domain/Settings/SiteOptionRepository.php';
if (file_exists($sampleFile)) {
    $content = file_get_contents($sampleFile);
    $lines = explode("\n", $content);
    echo "<p>First 20 lines of SiteOptionRepository.php:</p>";
    echo "<pre>";
    foreach (array_slice($lines, 0, 20) as $num => $line) {
        $color = preg_match('/readonly/', $line) ? 'background:#fee;' : '';
        echo "<span style='$color'>" . ($num + 1) . ": " . htmlspecialchars($line) . "</span>\n";
    }
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>DELETE THIS FILE after checking!</strong></p>";
?>

