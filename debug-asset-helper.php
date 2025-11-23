<?php
/**
 * Debug Asset Helper
 * Visit: http://localhost/s3vgroup/debug-asset-helper.php
 * DELETE after testing!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Asset Helper Debug</h1>";
echo "<hr>";

// Load bootstrap
require_once __DIR__ . '/bootstrap/app.php';

echo "<h2>1. Check if AssetHelper class exists</h2>";
if (class_exists('App\Support\AssetHelper')) {
    echo "✅ AssetHelper class exists<br>";
} else {
    echo "❌ AssetHelper class NOT found<br>";
    echo "Trying to load manually...<br>";
    require_once __DIR__ . '/app/Support/AssetHelper.php';
    if (class_exists('App\Support\AssetHelper')) {
        echo "✅ AssetHelper loaded manually<br>";
    } else {
        echo "❌ Still not found<br>";
    }
}

echo "<h2>2. Check if asset() function exists</h2>";
if (function_exists('asset')) {
    echo "✅ asset() function exists<br>";
} else {
    echo "❌ asset() function NOT found<br>";
}

echo "<h2>3. Check if base_url() function exists</h2>";
if (function_exists('base_url')) {
    echo "✅ base_url() function exists<br>";
} else {
    echo "❌ base_url() function NOT found<br>";
}

echo "<h2>4. Test AssetHelper directly</h2>";
try {
    $basePath = \App\Support\AssetHelper::basePath();
    echo "Base Path: <code>" . htmlspecialchars($basePath ?: '(root)') . "</code><br>";
    
    $assetUrl = \App\Support\AssetHelper::asset('includes/css/frontend.css');
    echo "Asset URL: <code>" . htmlspecialchars($assetUrl) . "</code><br>";
    
    $pageUrl = \App\Support\AssetHelper::url('products.php');
    echo "Page URL: <code>" . htmlspecialchars($pageUrl) . "</code><br>";
} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "Stack trace:<br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>5. Test helper functions</h2>";
try {
    if (function_exists('asset')) {
        $assetUrl = asset('includes/css/frontend.css');
        echo "asset() result: <code>" . htmlspecialchars($assetUrl) . "</code><br>";
    }
    
    if (function_exists('base_url')) {
        $pageUrl = base_url('products.php');
        echo "base_url() result: <code>" . htmlspecialchars($pageUrl) . "</code><br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<h2>6. Server Variables</h2>";
echo "<pre>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><strong>⚠️ DELETE this file after testing!</strong></p>";
?>

