<?php
/**
 * Debug Blank Page Issue
 * Visit: https://s3vgroup.com/debug-blank-page.php
 * DELETE after fixing!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Debug: Blank Page Issue</h1>";
echo "<hr>";

// Test 1: Basic PHP
echo "<h2>1. Basic PHP Test</h2>";
echo "✅ PHP is working<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test 2: Database connection
echo "<h2>2. Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = getDB();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test 3: Site config
echo "<h2>3. Site Configuration</h2>";
try {
    require_once __DIR__ . '/config/site.php';
    echo "✅ Site config loaded<br>";
    echo "Site URL: " . ($siteConfig['url'] ?? 'NOT SET') . "<br>";
} catch (Exception $e) {
    echo "❌ Site config error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test 4: Bootstrap
echo "<h2>4. Bootstrap App</h2>";
try {
    require_once __DIR__ . '/bootstrap/app.php';
    echo "✅ Bootstrap loaded<br>";
    
    if (function_exists('option')) {
        echo "✅ option() function exists<br>";
        $testOption = option('primary_color', '#0b3a63');
        echo "Test option value: " . htmlspecialchars($testOption) . "<br>";
    } else {
        echo "❌ option() function does NOT exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Bootstrap error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test 5: Functions
echo "<h2>5. Functions</h2>";
try {
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ Functions loaded<br>";
} catch (Exception $e) {
    echo "❌ Functions error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test 6: Header inclusion
echo "<h2>6. Header Include Test</h2>";
try {
    ob_start();
    $pageTitle = 'Debug Test';
    $pageDescription = 'Testing header inclusion';
    include __DIR__ . '/includes/header.php';
    $headerOutput = ob_get_clean();
    
    if (strlen($headerOutput) > 0) {
        echo "✅ Header loaded (output length: " . strlen($headerOutput) . " bytes)<br>";
        echo "First 200 chars: <pre>" . htmlspecialchars(substr($headerOutput, 0, 200)) . "</pre>";
    } else {
        echo "⚠️ Header loaded but output is empty<br>";
    }
} catch (Exception $e) {
    echo "❌ Header error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test 7: Output buffering
echo "<h2>7. Output Buffering Test</h2>";
if (ob_get_level() > 0) {
    echo "⚠️ Output buffering is active (level: " . ob_get_level() . ")<br>";
    ob_end_flush();
} else {
    echo "✅ No output buffering active<br>";
}

// Test 8: Error log check
echo "<h2>8. Recent PHP Errors</h2>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lastLines = file_get_contents($errorLog);
    $lines = explode("\n", $lastLines);
    $recentErrors = array_slice($lines, -10);
    echo "Recent errors:<pre>" . htmlspecialchars(implode("\n", $recentErrors)) . "</pre>";
} else {
    echo "⚠️ Error log not found or not configured<br>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all tests passed, the issue might be with:</p>";
echo "<ul>";
echo "<li>Output buffering issues</li>";
echo "<li>Fatal errors in index.php after header</li>";
echo "<li>Missing closing PHP tags or whitespace</li>";
echo "<li>Header redirects or output suppression</li>";
echo "</ul>";
echo "<p><strong>⚠️ DELETE this file after testing!</strong></p>";
?>

