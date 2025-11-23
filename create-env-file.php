<?php
/**
 * Quick .env File Creator for cPanel
 * 
 * Upload this to public_html/ and visit it once to create .env file
 * DELETE THIS FILE after use for security!
 */

// Security: Only allow if file doesn't exist or if accessed directly
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !isset($_GET['create'])) {
    die('Access denied');
}

$envFile = __DIR__ . '/.env';
$envContent = <<<'ENV'
# Live Server Database Configuration
# Auto-generated - DO NOT EDIT MANUALLY

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=s3vgroup_website
DB_USERNAME=s3vgroup_main
DB_PASSWORD=ASDasd12345$$$%%%
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Site Configuration
SITE_URL=https://s3vgroup.com
ENV;

if (file_exists($envFile)) {
    echo "<h1>⚠️ .env file already exists!</h1>";
    echo "<p>The .env file already exists. If you want to recreate it, delete it first.</p>";
    echo "<p>Current .env file location: <code>$envFile</code></p>";
} else {
    if (file_put_contents($envFile, $envContent)) {
        // Set permissions
        chmod($envFile, 0644);
        
        echo "<h1>✅ .env file created successfully!</h1>";
        echo "<p>The .env file has been created with your database credentials.</p>";
        echo "<p>File location: <code>$envFile</code></p>";
        echo "<p><strong>⚠️ IMPORTANT: Delete this file (create-env-file.php) now for security!</strong></p>";
        echo "<p><a href='/'>Test your website</a></p>";
    } else {
        echo "<h1>❌ Failed to create .env file</h1>";
        echo "<p>Please check file permissions. The directory must be writable.</p>";
    }
}

echo "<hr>";
echo "<h2>Database Configuration Created:</h2>";
echo "<pre>";
echo "Database: s3vgroup_website\n";
echo "Username: s3vgroup_main\n";
echo "Password: ********\n";
echo "Host: localhost\n";
echo "</pre>";
?>

