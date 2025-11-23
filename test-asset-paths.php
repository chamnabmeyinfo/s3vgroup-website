<?php
/**
 * Test Asset Path Detection
 * Visit: http://localhost/s3vgroup/test-asset-paths.php
 * Or: https://s3vgroup.com/test-asset-paths.php
 * DELETE after testing!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/bootstrap/app.php';

use App\Support\AssetHelper;

echo "<h1>Asset Path Detection Test</h1>";
echo "<hr>";

echo "<h2>Server Variables</h2>";
echo "<pre>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "</pre>";

echo "<h2>Detected Base Path</h2>";
$basePath = AssetHelper::basePath();
echo "<p><strong>Base Path:</strong> <code>" . htmlspecialchars($basePath ?: '(root)') . "</code></p>";

echo "<h2>Test Asset URLs</h2>";
echo "<ul>";
echo "<li>CSS: <code>" . htmlspecialchars(asset('includes/css/frontend.css')) . "</code></li>";
echo "<li>JS: <code>" . htmlspecialchars(asset('includes/js/modern.js')) . "</code></li>";
echo "<li>Home: <code>" . htmlspecialchars(base_url('/')) . "</code></li>";
echo "<li>Products: <code>" . htmlspecialchars(base_url('products.php')) . "</code></li>";
echo "</ul>";

echo "<h2>Test Links</h2>";
echo "<p><a href='" . htmlspecialchars(asset('includes/css/frontend.css')) . "'>Test CSS Link</a></p>";
echo "<p><a href='" . htmlspecialchars(base_url('products.php')) . "'>Test Products Link</a></p>";

echo "<h2>Expected Results</h2>";
echo "<ul>";
echo "<li><strong>On localhost (XAMPP):</strong> Base path should be <code>/s3vgroup</code></li>";
echo "<li><strong>On live server:</strong> Base path should be empty (root)</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>⚠️ DELETE this file after testing!</strong></p>";
?>

