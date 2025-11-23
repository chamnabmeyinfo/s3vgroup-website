<?php
/**
 * Test Live Database Connection
 * 
 * Use this to verify your config/database.live.php is set up correctly
 * 
 * Usage:
 *   php bin/test-live-connection.php
 */

require_once __DIR__ . '/../bootstrap/app.php';

echo "\n";
echo "═══════════════════════════════════════\n";
echo "  Testing Live Database Connection\n";
echo "═══════════════════════════════════════\n\n";

$liveConfigFile = __DIR__ . '/../config/database.live.php';

if (!file_exists($liveConfigFile)) {
    echo "❌ ERROR: config/database.live.php not found!\n";
    echo "   Please create it from config/database.live.php.example\n";
    exit(1);
}

echo "✅ Config file found: config/database.live.php\n\n";

$liveDbConfig = require $liveConfigFile;

if (!is_array($liveDbConfig)) {
    echo "❌ ERROR: Config file must return an array!\n";
    echo "   Current return type: " . gettype($liveDbConfig) . "\n";
    exit(1);
}

echo "✅ Config file returns array\n\n";

// Support both naming conventions
$host = $liveDbConfig['host'] ?? $liveDbConfig['hostname'] ?? null;
$database = $liveDbConfig['database'] ?? $liveDbConfig['name'] ?? $liveDbConfig['dbname'] ?? null;
$username = $liveDbConfig['username'] ?? $liveDbConfig['user'] ?? null;
$password = $liveDbConfig['password'] ?? $liveDbConfig['pass'] ?? null;

echo "Configuration:\n";
echo "  Host: " . ($host ?: "❌ MISSING") . "\n";
echo "  Database: " . ($database ?: "❌ MISSING") . "\n";
echo "  Username: " . ($username ?: "❌ MISSING") . "\n";
echo "  Password: " . ($password ? "***" : "❌ MISSING") . "\n\n";

if (empty($host) || empty($database) || empty($username)) {
    echo "❌ ERROR: Missing required configuration!\n";
    echo "   Required: host, database, username, password\n";
    exit(1);
}

echo "✅ All required fields present\n\n";
echo "Attempting connection...\n\n";

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $host,
        $database
    );
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    
    echo "✅ Connection successful!\n\n";
    
    // Test query
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "✅ Connected to database: $dbName\n";
    
    // Get table count
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Found " . count($tables) . " table(s)\n";
    
    if (count($tables) > 0) {
        echo "\nTables:\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════\n";
    echo "✅ Connection test PASSED!\n";
    echo "   Your config/database.live.php is working correctly.\n";
    echo "   You can now run: php bin/auto-sync-schema.php\n";
    echo "═══════════════════════════════════════\n\n";
    
} catch (PDOException $e) {
    echo "❌ Connection FAILED!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "Common issues:\n";
    echo "  1. Wrong host - Try 'localhost' for cPanel, or your server IP\n";
    echo "  2. Wrong database name - Check cPanel MySQL Databases\n";
    echo "  3. Wrong username/password - Verify in cPanel\n";
    echo "  4. Remote MySQL not enabled - Check cPanel → Remote MySQL\n";
    echo "  5. IP not whitelisted - Add your IP in cPanel → Remote MySQL\n";
    echo "  6. Firewall blocking - Check server firewall settings\n\n";
    
    echo "Troubleshooting:\n";
    echo "  - Test connection in phpMyAdmin first\n";
    echo "  - Verify credentials in cPanel → MySQL Databases\n";
    echo "  - Check cPanel → Remote MySQL (if connecting remotely)\n\n";
    
    exit(1);
}

