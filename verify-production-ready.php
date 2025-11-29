<?php
/**
 * Production Readiness Verification
 * Run this to verify all critical files are correct
 * DELETE THIS FILE AFTER VERIFICATION!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Production Readiness Verification</h1>";

$errors = [];
$warnings = [];
$success = [];

// 1. Check critical files exist
echo "<h2>1. Critical Files Check</h2>";
$criticalFiles = [
    'index.php' => 'Homepage',
    'ae-load.php' => 'Bootstrap file',
    'ae-includes/footer.php' => 'Footer template',
    'ae-includes/header.php' => 'Header template',
    'ae-includes/functions.php' => 'Core functions',
    'config/database.php.example' => 'Database config template',
    'config/site.php.example' => 'Site config template',
];

foreach ($criticalFiles as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $success[] = "✓ $description ($file) exists";
    } else {
        $errors[] = "✗ $description ($file) MISSING!";
    }
}

// 2. Check for duplicate e() function
echo "<h2>2. Function e() Check</h2>";
$footerContent = file_get_contents(__DIR__ . '/ae-includes/footer.php');
$functionsContent = file_get_contents(__DIR__ . '/ae-includes/functions.php');

// Check footer.php doesn't define e()
if (preg_match('/function\s+e\s*\(/', $footerContent) && !preg_match('/if\s*\(\s*!function_exists\s*\(\s*[\'"]e[\'"]/', $footerContent)) {
    $errors[] = "✗ footer.php defines e() function without safety check!";
} else {
    $success[] = "✓ footer.php doesn't define e() unsafely";
}

// Check functions.php has safety check
if (preg_match('/if\s*\(\s*!function_exists\s*\(\s*[\'"]e[\'"]/', $functionsContent)) {
    $success[] = "✓ functions.php has safety check for e()";
} else {
    $warnings[] = "⚠ functions.php may not have safety check for e()";
}

// 3. Check widgets are disabled
echo "<h2>3. Widget Status Check</h2>";
$widgetChecks = [
    'loading-screen' => '/widgets\/loading-screen\.php/',
    'mobile-app-header' => '/widgets\/mobile-app-header\.php/',
    'secondary-menu' => '/widgets\/secondary-menu\.php/',
    'bottom-nav' => '/bottom-nav/',
];

foreach ($widgetChecks as $widget => $pattern) {
    if (preg_match($pattern, $footerContent) || preg_match($pattern, file_get_contents(__DIR__ . '/ae-includes/header.php'))) {
        // Check if it's commented out
        $fullContent = $footerContent . file_get_contents(__DIR__ . '/ae-includes/header.php');
        if (preg_match('/\/\/\s*DISABLED.*' . preg_quote($widget, '/') . '/i', $fullContent) || 
            preg_match('/\/\*.*' . preg_quote($widget, '/') . '.*\*\//is', $fullContent)) {
            $success[] = "✓ $widget widget is disabled/commented";
        } else {
            $warnings[] = "⚠ $widget widget may still be active";
        }
    } else {
        $success[] = "✓ $widget widget not found (good)";
    }
}

// 4. Check index.php has fallback
echo "<h2>4. Configuration Fallback Check</h2>";
$indexContent = file_get_contents(__DIR__ . '/index.php');
if (preg_match('/if\s*\(\s*file_exists.*config\/site\.php/', $indexContent)) {
    $success[] = "✓ index.php has fallback for missing config/site.php";
} else {
    $warnings[] = "⚠ index.php may not have fallback for config/site.php";
}

// 5. Check ae-load.php loads functions.php
echo "<h2>5. Bootstrap Check</h2>";
$aeLoadContent = file_get_contents(__DIR__ . '/ae-load.php');
if (preg_match('/functions\.php/', $aeLoadContent)) {
    $success[] = "✓ ae-load.php loads functions.php";
} else {
    $warnings[] = "⚠ ae-load.php may not load functions.php early";
}

// 6. Check for temporary files
echo "<h2>6. Temporary Files Check</h2>";
$tempFiles = [
    'check-errors.php',
    'diagnose-production.php',
    'HOTFIX-e-function.php',
    'fix-500-error.php',
    'create-database-config.php',
    'create-site-config.php',
];

foreach ($tempFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $warnings[] = "⚠ Temporary file found: $file (should be deleted)";
    } else {
        $success[] = "✓ $file not found (good)";
    }
}

// 7. Check .gitignore
echo "<h2>7. .gitignore Check</h2>";
$gitignore = file_get_contents(__DIR__ . '/.gitignore');
if (strpos($gitignore, 'check-errors.php') !== false) {
    $success[] = "✓ .gitignore excludes temporary files";
} else {
    $warnings[] = "⚠ .gitignore may not exclude all temporary files";
}

// Display Results
echo "<h2>Results</h2>";

if (!empty($success)) {
    echo "<h3 style='color:green'>✓ Success (" . count($success) . ")</h3>";
    echo "<ul>";
    foreach ($success as $msg) {
        echo "<li style='color:green'>$msg</li>";
    }
    echo "</ul>";
}

if (!empty($warnings)) {
    echo "<h3 style='color:orange'>⚠ Warnings (" . count($warnings) . ")</h3>";
    echo "<ul>";
    foreach ($warnings as $msg) {
        echo "<li style='color:orange'>$msg</li>";
    }
    echo "</ul>";
}

if (!empty($errors)) {
    echo "<h3 style='color:red'>✗ Errors (" . count($errors) . ")</h3>";
    echo "<ul>";
    foreach ($errors as $msg) {
        echo "<li style='color:red'>$msg</li>";
    }
    echo "</ul>";
}

// Final Status
echo "<hr>";
if (empty($errors)) {
    echo "<h2 style='color:green'>✅ Production Ready!</h2>";
    echo "<p>All critical checks passed. The code is ready for deployment.</p>";
} else {
    echo "<h2 style='color:red'>❌ Not Production Ready</h2>";
    echo "<p>Please fix the errors above before deploying.</p>";
}

echo "<p><strong>DELETE THIS FILE after verification!</strong></p>";
?>

