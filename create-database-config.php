<?php
/**
 * Database Configuration Creator
 * Run this ONCE after cloning to create your database.php file
 * DELETE THIS FILE after creating the config!
 */

$configFile = __DIR__ . '/config/database.php';

if (file_exists($configFile)) {
    die("
    <h1>Database Config Already Exists</h1>
    <p>config/database.php already exists. If you want to recreate it, delete it first.</p>
    <p><a href='/'>Go to homepage</a></p>
    ");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create config directory
    if (!is_dir(__DIR__ . '/config')) {
        mkdir(__DIR__ . '/config', 0755, true);
    }
    
    // Create database.php file
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    
    if (empty($dbName) || empty($dbUser)) {
        die("<p style='color:red'>Database name and user are required!</p>");
    }
    
    $configContent = "<?php
/**
 * Database Configuration
 * AUTO-GENERATED - Edit this file with your production credentials
 */

define('DB_HOST', " . var_export($dbHost, true) . ");
define('DB_NAME', " . var_export($dbName, true) . ");
define('DB_USER', " . var_export($dbUser, true) . ");
define('DB_PASS', " . var_export($dbPass, true) . ");
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO Database Connection
 */
function getDB() {
    static \$pdo = null;
    
    if (\$pdo === null) {
        try {
            \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;
            \$options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
        } catch (PDOException \$e) {
            error_log(\"Database connection failed: \" . \$e->getMessage());
            die(\"Database connection failed. Please check your configuration.\");
        }
    }
    
    return \$pdo;
}
";
    
    if (file_put_contents($configFile, $configContent)) {
        chmod($configFile, 0644);
        echo "<h1 style='color:green'>✅ Database Config Created!</h1>";
        echo "<p>The file <code>config/database.php</code> has been created.</p>";
        echo "<p><strong>IMPORTANT: Delete this file (create-database-config.php) now for security!</strong></p>";
        echo "<p><a href='/'>Go to homepage</a></p>";
    } else {
        echo "<p style='color:red'>Failed to create config file. Check directory permissions.</p>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Database Configuration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Create Database Configuration</h1>
    
    <div class="warning">
        <strong>⚠️ Security Warning:</strong> Delete this file after creating your database config!
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="db_host">Database Host:</label>
            <input type="text" id="db_host" name="db_host" value="localhost" required>
        </div>
        
        <div class="form-group">
            <label for="db_name">Database Name:</label>
            <input type="text" id="db_name" name="db_name" required>
        </div>
        
        <div class="form-group">
            <label for="db_user">Database Username:</label>
            <input type="text" id="db_user" name="db_user" required>
        </div>
        
        <div class="form-group">
            <label for="db_pass">Database Password:</label>
            <input type="password" id="db_pass" name="db_pass">
        </div>
        
        <button type="submit">Create Database Config</button>
    </form>
</body>
</html>

