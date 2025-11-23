<?php
/**
 * Test Homepage - Diagnose 500 Error
 * Visit: https://s3vgroup.com/test-homepage.php
 * DELETE after fixing!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Homepage Diagnostic Test</h1>";
echo "<hr>";

// Step 1: Test database.php load
echo "<h2>Step 1: Loading database.php</h2>";
try {
    $dbConfig = require __DIR__ . '/config/database.php';
    echo "✅ database.php loaded<br>";
    echo "Config: <pre>" . print_r($dbConfig, true) . "</pre>";
} catch (Exception $e) {
    echo "❌ Error loading database.php: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    die();
}

// Step 2: Test getDB() function
echo "<h2>Step 2: Testing getDB() function</h2>";
if (function_exists('getDB')) {
    echo "✅ getDB() function exists<br>";
    
    try {
        $db = getDB();
        echo "✅ Database connection successful!<br>";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "<br>";
        die();
    }
} else {
    echo "❌ getDB() function does NOT exist!<br>";
    die();
}

// Step 3: Test site.php load
echo "<h2>Step 3: Loading site.php</h2>";
try {
    require __DIR__ . '/config/site.php';
    echo "✅ site.php loaded<br>";
    echo "Site URL: " . ($siteConfig['url'] ?? 'NOT SET') . "<br>";
} catch (Exception $e) {
    echo "❌ Error loading site.php: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Step 4: Test functions.php load
echo "<h2>Step 4: Loading functions.php</h2>";
try {
    require __DIR__ . '/includes/functions.php';
    echo "✅ functions.php loaded<br>";
} catch (Exception $e) {
    echo "❌ Error loading functions.php: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    die();
}

// Step 5: Test database tables exist
echo "<h2>Step 5: Checking database tables</h2>";
try {
    $tables = ['categories', 'products', 'quote_requests', 'site_options'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✅ Table '$table' exists (has $count rows)<br>";
        } catch (PDOException $e) {
            echo "❌ Table '$table' does NOT exist or error: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Step 6: Test getFeaturedCategories function
echo "<h2>Step 6: Testing getFeaturedCategories()</h2>";
if (function_exists('getFeaturedCategories')) {
    try {
        $categories = getFeaturedCategories($db, 12);
        echo "✅ getFeaturedCategories() works! Found " . count($categories) . " categories<br>";
    } catch (Exception $e) {
        echo "❌ getFeaturedCategories() failed: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
} else {
    echo "❌ getFeaturedCategories() function does NOT exist!<br>";
}

// Step 7: Test getFeaturedProducts function
echo "<h2>Step 7: Testing getFeaturedProducts()</h2>";
if (function_exists('getFeaturedProducts')) {
    try {
        $products = getFeaturedProducts($db, 6);
        echo "✅ getFeaturedProducts() works! Found " . count($products) . " products<br>";
    } catch (Exception $e) {
        echo "❌ getFeaturedProducts() failed: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
} else {
    echo "❌ getFeaturedProducts() function does NOT exist!<br>";
}

// Step 8: Test bootstrap/app.php
echo "<h2>Step 8: Testing bootstrap/app.php</h2>";
try {
    require __DIR__ . '/bootstrap/app.php';
    echo "✅ bootstrap/app.php loaded<br>";
    
    // Test option() function
    if (function_exists('option')) {
        echo "✅ option() function exists<br>";
        $testOption = option('primary_color', '#0b3a63');
        echo "Test option value: " . htmlspecialchars($testOption) . "<br>";
    } else {
        echo "⚠️ option() function does NOT exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading bootstrap/app.php: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Step 9: Test header.php
echo "<h2>Step 9: Testing includes/header.php</h2>";
$pageTitle = 'Test';
$pageDescription = 'Test description';
try {
    ob_start();
    include __DIR__ . '/includes/header.php';
    $headerOutput = ob_get_clean();
    echo "✅ header.php loaded (output length: " . strlen($headerOutput) . " bytes)<br>";
} catch (Exception $e) {
    echo "❌ Error loading header.php: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all steps passed, the issue might be in index.php itself. Check the exact line that fails.</p>";
echo "<p><strong>⚠️ DELETE this file after testing!</strong></p>";
?>

