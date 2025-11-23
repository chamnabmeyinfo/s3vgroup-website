<?php
/**
 * Database Manager - Complete Database Management Tool
 * 
 * Allows you to create, update, delete, and manage database tables and data
 * 
 * Usage:
 *   php bin/db-manager.php list-tables                    # List all tables
 *   php bin/db-manager.php describe table_name             # Show table structure
 *   php bin/db-manager.php add-column table column type   # Add column
 *   php bin/db-manager.php sync-schema                    # Sync schema to live
 *   php bin/db-manager.php query "SELECT * FROM table"    # Run custom query
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

// Colors
class Colors {
    const RESET = "\033[0m";
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

function printSuccess(string $message): void {
    echo Colors::GREEN . "✅ $message" . Colors::RESET . "\n";
}

function printError(string $message): void {
    echo Colors::RED . "❌ $message" . Colors::RESET . "\n";
}

function printInfo(string $message): void {
    echo Colors::CYAN . "ℹ️  $message" . Colors::RESET . "\n";
}

function printHeader(string $message): void {
    echo "\n" . Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::BOLD . Colors::BLUE . "  $message" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
}

function connectToLiveDatabase(): ?PDO {
    $liveConfigFile = __DIR__ . '/../config/database.live.php';
    
    if (!file_exists($liveConfigFile)) {
        return null;
    }
    
    $liveDbConfig = require $liveConfigFile;
    
    if (!is_array($liveDbConfig)) {
        return null;
    }
    
    $host = $liveDbConfig['host'] ?? $liveDbConfig['hostname'] ?? 'localhost';
    $database = $liveDbConfig['database'] ?? $liveDbConfig['name'] ?? $liveDbConfig['dbname'] ?? '';
    $username = $liveDbConfig['username'] ?? $liveDbConfig['user'] ?? '';
    $password = $liveDbConfig['password'] ?? $liveDbConfig['pass'] ?? '';
    
    if (empty($database) || empty($username)) {
        return null;
    }
    
    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $database);
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

function listTables(PDO $db, string $database): void {
    printHeader("Tables in Database: $database");
    
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        printInfo("No tables found.");
        return;
    }
    
    foreach ($tables as $index => $table) {
        $rowCount = $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo sprintf("%2d. %-30s (%s rows)\n", $index + 1, $table, number_format($rowCount));
    }
}

function describeTable(PDO $db, string $table): void {
    printHeader("Table Structure: $table");
    
    try {
        $stmt = $db->query("DESCRIBE `{$table}`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        printf("%-20s %-20s %-10s %-10s %-10s %-10s\n", "Field", "Type", "Null", "Key", "Default", "Extra");
        echo str_repeat("-", 90) . "\n";
        
        foreach ($columns as $col) {
            printf("%-20s %-20s %-10s %-10s %-10s %-10s\n",
                $col['Field'],
                $col['Type'],
                $col['Null'],
                $col['Key'],
                $col['Default'] ?? 'NULL',
                $col['Extra']
            );
        }
    } catch (PDOException $e) {
        printError("Table '$table' not found: " . $e->getMessage());
    }
}

function addColumn(PDO $db, string $table, string $column, string $type, ?string $after = null): void {
    printHeader("Adding Column to Table: $table");
    
    try {
        // Check if column exists
        $stmt = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        if ($stmt->fetch()) {
            printError("Column '$column' already exists in table '$table'");
            return;
        }
        
        $afterClause = $after ? "AFTER `{$after}`" : "";
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type} {$afterClause}";
        
        $db->exec($sql);
        printSuccess("Column '$column' added successfully to table '$table'");
    } catch (PDOException $e) {
        printError("Failed to add column: " . $e->getMessage());
    }
}

function syncSchema(): void {
    printHeader("Syncing Schema to Live Database");
    
    // Use the existing auto-sync-schema.php script
    $script = __DIR__ . '/auto-sync-schema.php';
    if (file_exists($script)) {
        passthru("php " . escapeshellarg($script));
    } else {
        printError("Auto-sync script not found!");
    }
}

function runQuery(PDO $db, string $query): void {
    printHeader("Running Query");
    printInfo("Query: $query\n");
    
    try {
        $stmt = $db->query($query);
        
        // Check if it's a SELECT query
        if (stripos($query, 'SELECT') === 0) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                printInfo("No results found.");
                return;
            }
            
            // Print headers
            $headers = array_keys($results[0]);
            echo implode(" | ", $headers) . "\n";
            echo str_repeat("-", strlen(implode(" | ", $headers))) . "\n";
            
            // Print rows (limit to 20 for readability)
            $limit = min(20, count($results));
            for ($i = 0; $i < $limit; $i++) {
                $row = array_map(function($val) {
                    return is_null($val) ? 'NULL' : (strlen($val) > 30 ? substr($val, 0, 30) . '...' : $val);
                }, $results[$i]);
                echo implode(" | ", $row) . "\n";
            }
            
            if (count($results) > 20) {
                printInfo("\n... and " . (count($results) - 20) . " more rows");
            }
        } else {
            $affected = $stmt->rowCount();
            printSuccess("Query executed successfully. Rows affected: $affected");
        }
    } catch (PDOException $e) {
        printError("Query failed: " . $e->getMessage());
    }
}

function showHelp(): void {
    printHeader("Database Manager - Help");
    
    echo "Available Commands:\n\n";
    echo "  list-tables [local|live]          List all tables\n";
    echo "  describe <table> [local|live]     Show table structure\n";
    echo "  add-column <table> <column> <type> [after] [local|live]  Add column to table\n";
    echo "  sync-schema                       Sync schema from local to live\n";
    echo "  query \"SQL\" [local|live]         Run custom SQL query\n";
    echo "  help                              Show this help\n\n";
    
    echo "Examples:\n\n";
    echo "  php bin/db-manager.php list-tables live\n";
    echo "  php bin/db-manager.php describe team_members local\n";
    echo "  php bin/db-manager.php add-column team_members department \"VARCHAR(255) NULL\" title live\n";
    echo "  php bin/db-manager.php query \"SELECT * FROM team_members LIMIT 5\" live\n";
    echo "  php bin/db-manager.php sync-schema\n\n";
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$args = array_slice($argv, 1);

if (empty($args)) {
    showHelp();
    exit(0);
}

$command = $args[0];
$target = 'local'; // default to local

// Check if last argument is 'local' or 'live'
if (in_array(end($args), ['local', 'live'])) {
    $target = array_pop($args);
    $args = array_slice($args, 1);
} else {
    $args = array_slice($args, 1);
}

// Get database connection
if ($target === 'live') {
    $db = connectToLiveDatabase();
    if (!$db) {
        printError("Failed to connect to live database. Check config/database.live.php");
        exit(1);
    }
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    printInfo("Connected to LIVE database: $dbName");
} else {
    $db = getDB();
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    printInfo("Connected to LOCAL database: $dbName");
}

try {
    switch ($command) {
        case 'list-tables':
            listTables($db, $dbName);
            break;
            
        case 'describe':
            if (empty($args)) {
                printError("Table name required. Usage: describe <table>");
                exit(1);
            }
            describeTable($db, $args[0]);
            break;
            
        case 'add-column':
            if (count($args) < 3) {
                printError("Usage: add-column <table> <column> <type> [after_column]");
                exit(1);
            }
            $after = $args[3] ?? null;
            addColumn($db, $args[0], $args[1], $args[2], $after);
            break;
            
        case 'sync-schema':
            syncSchema();
            break;
            
        case 'query':
            if (empty($args)) {
                printError("SQL query required. Usage: query \"SELECT * FROM table\"");
                exit(1);
            }
            runQuery($db, implode(' ', $args));
            break;
            
        case 'help':
            showHelp();
            break;
            
        default:
            printError("Unknown command: $command");
            showHelp();
            exit(1);
    }
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

