<?php
/**
 * HOTFIX: Fix e() function redeclaration error
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to your production server at: /home/s3vgroup/public_html/
 * 2. Visit: https://s3vgroup.com/HOTFIX-e-function.php
 * 3. This will fix the footer.php file directly
 * 4. DELETE this file after running it!
 */

echo "<h1>Hotfix: Fix e() Function Error</h1>";

$footerFile = __DIR__ . '/ae-includes/footer.php';

if (!file_exists($footerFile)) {
    die("<p style='color:red'>ERROR: footer.php not found at: $footerFile</p>");
}

$footerContent = file_get_contents($footerFile);
$footerLines = explode("\n", $footerContent);

// Check if e() function is defined in footer.php
$hasEFunction = preg_match('/function\s+e\s*\(/', $footerContent);

if ($hasEFunction) {
    echo "<p style='color:orange'>Found e() function in footer.php - removing it...</p>";
    
    // Remove the e() function definition from footer.php
    // Find and remove lines that define e() function
    $newLines = [];
    $skipNext = 0;
    $inFunction = false;
    $braceCount = 0;
    
    foreach ($footerLines as $lineNum => $line) {
        // Check if this line starts a function e() definition
        if (preg_match('/^\s*if\s*\(\s*!function_exists\s*\(\s*[\'"]e[\'"]\s*\)\s*\)\s*\{?\s*$/', $line)) {
            $skipNext = 10; // Skip next 10 lines (function definition block)
            $inFunction = true;
            $braceCount = 1;
            echo "<p>Removing function definition starting at line " . ($lineNum + 1) . "</p>";
            continue;
        }
        
        if ($inFunction) {
            // Count braces to know when function ends
            $braceCount += substr_count($line, '{') - substr_count($line, '}');
            if ($braceCount <= 0 && strpos($line, '}') !== false) {
                $inFunction = false;
                continue; // Skip this closing brace
            }
            continue; // Skip lines inside function
        }
        
        // Check for direct function e() definition
        if (preg_match('/^\s*function\s+e\s*\(/', $line)) {
            echo "<p>Removing direct function definition at line " . ($lineNum + 1) . "</p>";
            // Skip this line and next few lines until closing brace
            $inFunction = true;
            $braceCount = substr_count($line, '{') - substr_count($line, '}');
            continue;
        }
        
        $newLines[] = $line;
    }
    
    // Add ensure functions.php is loaded at the top
    $newContent = implode("\n", $newLines);
    
    // Ensure functions.php is loaded early in footer.php
    if (strpos($newContent, 'require_once') !== false && strpos($newContent, 'functions.php') === false) {
        // Find the PHP opening tag and add after it
        $newContent = preg_replace(
            '/^(\s*<\?php\s*\n)/',
            '$1' . "    // Load functions.php FIRST to ensure e() function is available\n    if (file_exists(__DIR__ . '/functions.php')) {\n        require_once __DIR__ . '/functions.php';\n    }\n",
            $newContent,
            1
        );
    }
    
    // Backup original
    $backupFile = $footerFile . '.backup.' . date('Y-m-d-His');
    copy($footerFile, $backupFile);
    echo "<p>Backup created: " . basename($backupFile) . "</p>";
    
    // Write fixed content
    if (file_put_contents($footerFile, $newContent)) {
        echo "<p style='color:green'><strong>SUCCESS!</strong> footer.php has been fixed.</p>";
        echo "<p><a href='/'>Test the homepage</a></p>";
    } else {
        echo "<p style='color:red'><strong>ERROR:</strong> Could not write to footer.php. Check file permissions.</p>";
    }
} else {
    echo "<p style='color:green'>footer.php already looks correct (no e() function found).</p>";
    echo "<p>However, the error suggests production still has old code. Make sure you've pulled the latest changes from git.</p>";
}

echo "<hr>";
echo "<p><strong>DELETE THIS FILE AFTER FIXING!</strong></p>";
?>

