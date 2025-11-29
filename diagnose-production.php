<?php
/**
 * Production Diagnostic Script
 * Checks what's actually in the files on production
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Production File Diagnostic</h1>";

// Check footer.php
echo "<h2>footer.php Analysis:</h2>";
$footerFile = __DIR__ . '/ae-includes/footer.php';
if (file_exists($footerFile)) {
    $footerContent = file_get_contents($footerFile);
    $footerLines = explode("\n", $footerContent);
    
    echo "<p><strong>File exists:</strong> Yes</p>";
    echo "<p><strong>File size:</strong> " . filesize($footerFile) . " bytes</p>";
    echo "<p><strong>First 20 lines:</strong></p>";
    echo "<pre>";
    foreach (array_slice($footerLines, 0, 20) as $i => $line) {
        echo ($i + 1) . ": " . htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
    
    // Check if e() function is defined
    if (preg_match('/function\s+e\s*\(/', $footerContent)) {
        echo "<p style='color:red'><strong>ERROR:</strong> e() function IS defined in footer.php!</p>";
        preg_match_all('/function\s+e\s*\(/', $footerContent, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match) {
            $pos = $match[1];
            $lineNum = substr_count(substr($footerContent, 0, $pos), "\n") + 1;
            echo "<p>Found at line: $lineNum</p>";
        }
    } else {
        echo "<p style='color:green'><strong>OK:</strong> e() function is NOT defined in footer.php</p>";
    }
} else {
    echo "<p style='color:red'>File does not exist!</p>";
}

// Check functions.php
echo "<h2>functions.php Analysis:</h2>";
$functionsFile = __DIR__ . '/ae-includes/functions.php';
if (file_exists($functionsFile)) {
    $functionsContent = file_get_contents($functionsFile);
    $functionsLines = explode("\n", $functionsContent);
    
    echo "<p><strong>File exists:</strong> Yes</p>";
    
    // Check around line 107
    echo "<p><strong>Lines 105-110:</strong></p>";
    echo "<pre>";
    foreach (array_slice($functionsLines, 104, 6) as $i => $line) {
        echo ($i + 105) . ": " . htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
    
    // Check if e() function has safety check
    if (preg_match('/if\s*\(\s*!function_exists\s*\(\s*[\'"]e/', $functionsContent)) {
        echo "<p style='color:green'><strong>OK:</strong> e() function has safety check (function_exists)</p>";
    } else {
        echo "<p style='color:orange'><strong>WARNING:</strong> e() function may not have safety check</p>";
    }
} else {
    echo "<p style='color:red'>File does not exist!</p>";
}

// Check Git status (if git is available)
echo "<h2>Git Status:</h2>";
if (function_exists('exec')) {
    $gitStatus = [];
    exec('cd ' . escapeshellarg(__DIR__) . ' && git status 2>&1', $gitStatus);
    echo "<pre>" . implode("\n", $gitStatus) . "</pre>";
    
    $gitLog = [];
    exec('cd ' . escapeshellarg(__DIR__) . ' && git log --oneline -5 2>&1', $gitLog);
    echo "<h3>Recent Git Commits:</h3>";
    echo "<pre>" . implode("\n", $gitLog) . "</pre>";
} else {
    echo "<p>Cannot check git status (exec disabled)</p>";
}

echo "<hr>";
echo "<p><strong>Delete this file after debugging!</strong></p>";
?>

