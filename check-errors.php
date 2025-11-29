<?php
/**
 * Error Check Script
 * Run this to check for PHP errors on production
 * Access via: https://s3vgroup.com/check-errors.php
 * DELETE THIS FILE AFTER DEBUGGING!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>PHP Error Check</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Check critical files
$files = [
    'ae-includes/footer.php',
    'ae-includes/widgets/bottom-nav.php',
    'ae-includes/widgets/bottom-nav-safe.php',
    'index.php'
];

echo "<h2>File Syntax Check:</h2>";
foreach ($files as $file) {
    if (file_exists($file)) {
        $output = [];
        $return = 0;
        exec("php -l $file 2>&1", $output, $return);
        if ($return === 0) {
            echo "<p style='color:green'>✓ $file - OK</p>";
        } else {
            echo "<p style='color:red'>✗ $file - ERROR:</p>";
            echo "<pre>" . implode("\n", $output) . "</pre>";
        }
    } else {
        echo "<p style='color:orange'>⚠ $file - NOT FOUND</p>";
    }
}

// Test footer include
echo "<h2>Testing Footer Include:</h2>";
try {
    // Load functions.php FIRST before including footer.php
    if (file_exists('ae-includes/functions.php')) {
        require_once 'ae-includes/functions.php';
    }
    
    ob_start();
    $error = false;
    set_error_handler(function($errno, $errstr) use (&$error) {
        $error = "Error $errno: $errstr";
        return true;
    });
    
    if (file_exists('ae-includes/footer.php')) {
        include 'ae-includes/footer.php';
        $output = ob_get_clean();
        if ($error) {
            echo "<p style='color:red'>✗ Footer include error: $error</p>";
        } else {
            echo "<p style='color:green'>✓ Footer include - OK</p>";
        }
    }
    restore_error_handler();
} catch (Throwable $e) {
    echo "<p style='color:red'>✗ Footer fatal error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Delete this file after debugging!</strong></p>";
?>

