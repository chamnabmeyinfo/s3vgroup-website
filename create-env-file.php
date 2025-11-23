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

// Security: Prompt for credentials instead of hardcoding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_env'])) {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbDatabase = $_POST['db_database'] ?? '';
    $dbUsername = $_POST['db_username'] ?? '';
    $dbPassword = $_POST['db_password'] ?? '';
    $siteUrl = $_POST['site_url'] ?? 'https://s3vgroup.com';
    
    if (empty($dbDatabase) || empty($dbUsername) || empty($dbPassword)) {
        die('Error: Database credentials are required');
    }
    
    $envContent = <<<ENV
# Live Server Database Configuration
# Auto-generated - DO NOT EDIT MANUALLY

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT=3306
DB_DATABASE={$dbDatabase}
DB_USERNAME={$dbUsername}
DB_PASSWORD={$dbPassword}
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Site Configuration
SITE_URL={$siteUrl}
ENV;
} else {
    // Show form to collect credentials
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Create .env File</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input { width: 100%; padding: 8px; box-sizing: border-box; }
            button { background: #0b3a63; color: white; padding: 10px 20px; border: none; cursor: pointer; }
            .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h1>Create .env File</h1>
        <div class="warning">
            <strong>⚠️ Security Notice:</strong> This form will create a .env file with your database credentials. 
            Make sure to delete this file (create-env-file.php) after use!
        </div>
        <form method="POST">
            <input type="hidden" name="create_env" value="1">
            
            <div class="form-group">
                <label>Database Host:</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label>Database Name:</label>
                <input type="text" name="db_database" placeholder="s3vgroup_website" required>
            </div>
            
            <div class="form-group">
                <label>Database Username:</label>
                <input type="text" name="db_username" placeholder="s3vgroup_main" required>
            </div>
            
            <div class="form-group">
                <label>Database Password:</label>
                <input type="password" name="db_password" required>
            </div>
            
            <div class="form-group">
                <label>Site URL:</label>
                <input type="url" name="site_url" value="https://s3vgroup.com" required>
            </div>
            
            <button type="submit">Create .env File</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

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
echo "Database: " . htmlspecialchars($dbDatabase) . "\n";
echo "Username: " . htmlspecialchars($dbUsername) . "\n";
echo "Password: ********\n";
echo "Host: " . htmlspecialchars($dbHost) . "\n";
echo "</pre>";
?>

