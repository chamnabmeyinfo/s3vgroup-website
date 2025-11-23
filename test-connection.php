<?php
/**
 * Database Connection Test Script
 * Upload this to public_html/ and visit it in your browser
 * Delete this file after testing for security
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";
echo "<hr>";

// Step 1: Check if config files exist
echo "<h2>Step 1: Checking Configuration Files</h2>";

$configPath = __DIR__ . '/config/';
$databaseLocalFile = $configPath . 'database.local.php';
$databaseFile = $configPath . 'database.php';
$siteFile = $configPath . 'site.php';

echo "Config directory exists: " . (is_dir($configPath) ? "✅ Yes" : "❌ No") . "<br>";
echo "database.local.php exists: " . (file_exists($databaseLocalFile) ? "✅ Yes" : "❌ No") . "<br>";
echo "database.php exists: " . (file_exists($databaseFile) ? "✅ Yes" : "❌ No") . "<br>";
echo "site.php exists: " . (file_exists($siteFile) ? "✅ Yes" : "❌ No") . "<br>";

echo "<hr>";

// Step 2: Load database configuration
echo "<h2>Step 2: Loading Database Configuration</h2>";

if (file_exists($databaseLocalFile)) {
    echo "✅ Found database.local.php<br>";
    $dbConfig = require $databaseLocalFile;
    
    if (is_array($dbConfig)) {
        echo "✅ Configuration loaded successfully<br>";
        echo "<pre>";
        echo "Host: " . ($dbConfig['host'] ?? 'NOT SET') . "\n";
        echo "Database: " . ($dbConfig['database'] ?? 'NOT SET') . "\n";
        echo "Username: " . ($dbConfig['username'] ?? 'NOT SET') . "\n";
        echo "Password: " . (isset($dbConfig['password']) ? '***SET***' : 'NOT SET') . "\n";
        echo "</pre>";
    } else {
        echo "❌ Configuration file did not return an array<br>";
        $dbConfig = null;
    }
} else {
    echo "❌ database.local.php not found!<br>";
    echo "Please create it in: " . $databaseLocalFile . "<br>";
    $dbConfig = null;
}

echo "<hr>";

// Step 3: Test database connection
echo "<h2>Step 3: Testing Database Connection</h2>";

if ($dbConfig && isset($dbConfig['host'], $dbConfig['database'], $dbConfig['username'], $dbConfig['password'])) {
    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=" . ($dbConfig['charset'] ?? 'utf8mb4');
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        echo "Attempting connection...<br>";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        
        echo "✅ <strong>Database connection successful!</strong><br>";
        
        // Test query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch();
        echo "MySQL Version: " . $version['version'] . "<br>";
        
        // Check tables
        echo "<br><strong>Database Tables:</strong><br>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "✅ Found " . count($tables) . " table(s):<br>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . $table . "</li>";
            }
            echo "</ul>";
        } else {
            echo "⚠️ No tables found. You may need to import schema.sql<br>";
        }
        
    } catch (PDOException $e) {
        echo "❌ <strong>Database connection failed!</strong><br>";
        echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<br><strong>Troubleshooting:</strong><br>";
        echo "1. Check database credentials in database.local.php<br>";
        echo "2. Verify database name includes username prefix<br>";
        echo "3. Check database user has ALL PRIVILEGES<br>";
        echo "4. Verify database exists in cPanel MySQL Databases<br>";
    }
} else {
    echo "❌ Cannot test connection - missing configuration<br>";
}

echo "<hr>";

// Step 4: Check site configuration
echo "<h2>Step 4: Checking Site Configuration</h2>";

if (file_exists($siteFile)) {
    require $siteFile;
    echo "✅ site.php loaded<br>";
    
    if (isset($siteConfig)) {
        echo "Site Name: " . ($siteConfig['name'] ?? 'NOT SET') . "<br>";
        echo "Site URL: " . ($siteConfig['url'] ?? 'NOT SET') . "<br>";
        
        if (isset($siteConfig['url']) && strpos($siteConfig['url'], 'localhost') !== false) {
            echo "⚠️ <strong>Warning:</strong> Site URL still contains 'localhost'. Update it in site.php!<br>";
        }
    }
    
    if (defined('ADMIN_EMAIL')) {
        echo "Admin Email: " . ADMIN_EMAIL . "<br>";
    }
    
    if (defined('ADMIN_PASSWORD')) {
        if (ADMIN_PASSWORD === 'admin123') {
            echo "⚠️ <strong>Warning:</strong> Admin password is still default! Change it in site.php!<br>";
        } else {
            echo "✅ Admin password has been changed<br>";
        }
    }
} else {
    echo "❌ site.php not found!<br>";
}

echo "<hr>";

// Step 5: Check file permissions
echo "<h2>Step 5: Checking File Permissions</h2>";

$directories = [
    'config' => $configPath,
    'uploads' => __DIR__ . '/uploads/',
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo ucfirst($name) . " directory permissions: " . $perms . "<br>";
        if ($perms !== '0755') {
            echo "⚠️ Recommended: 755 for directories<br>";
        } else {
            echo "✅ Permissions are correct<br>";
        }
    } else {
        echo "⚠️ Directory doesn't exist: " . $name . "<br>";
    }
}

echo "<hr>";

// Step 6: PHP Information
echo "<h2>Step 6: PHP Information</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";

if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "⚠️ <strong>Warning:</strong> PHP version is below 7.4. Recommended: 7.4+<br>";
} else {
    echo "✅ PHP version is compatible<br>";
}

echo "PDO MySQL extension: " . (extension_loaded('pdo_mysql') ? "✅ Loaded" : "❌ Not loaded") . "<br>";

echo "<hr>";

// Step 7: Recommendations
echo "<h2>Step 7: Recommendations</h2>";
echo "<ul>";

if (!file_exists($databaseLocalFile)) {
    echo "<li>❌ Create database.local.php file</li>";
}

if (isset($siteConfig['url']) && strpos($siteConfig['url'], 'localhost') !== false) {
    echo "<li>⚠️ Update site URL in site.php to your live domain</li>";
}

if (defined('ADMIN_PASSWORD') && ADMIN_PASSWORD === 'admin123') {
    echo "<li>⚠️ Change admin password in site.php (currently using default)</li>";
}

echo "<li>✅ Delete this test file after testing: test-connection.php</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Test completed!</strong> Review the results above and fix any issues.</p>";
echo "<p style='color: red;'><strong>⚠️ IMPORTANT:</strong> Delete this file (test-connection.php) after testing for security!</p>";
?>

