<?php
/**
 * Automatic Schema Sync to Live (cPanel)
 * 
 * Automatically detects and applies schema changes from local to live database
 * Designed to run automatically (cron, scheduled task, etc.)
 * 
 * Usage:
 *   php bin/auto-sync-schema.php                    # Auto-sync all tables
 *   php bin/auto-sync-schema.php --table=team_members  # Sync specific table
 *   php bin/auto-sync-schema.php --quiet          # Minimal output (for cron)
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

// Colors for terminal output
class Colors {
    const RESET = "\033[0m";
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
}

function printSuccess(string $message, bool $quiet = false): void {
    if (!$quiet) echo Colors::GREEN . "✅ $message" . Colors::RESET . "\n";
}

function printError(string $message, bool $quiet = false): void {
    echo Colors::RED . "❌ $message" . Colors::RESET . "\n";
}

function printInfo(string $message, bool $quiet = false): void {
    if (!$quiet) echo Colors::CYAN . "ℹ️  $message" . Colors::RESET . "\n";
}

function logMessage(string $message, string $logFile = null): void {
    if ($logFile) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}

/**
 * Get all columns in a table with their definitions
 */
function getTableColumns(PDO $db, string $table): array {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM `{$table}`");
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[$row['Field']] = [
                'type' => $row['Type'],
                'null' => $row['Null'] === 'YES',
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra'],
            ];
        }
        return $columns;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Generate ALTER TABLE statement for adding a column
 */
function generateAlterStatement(string $table, string $column, array $columnDef, ?string $afterColumn = null): string {
    $type = $columnDef['type'];
    $null = $columnDef['null'] ? 'NULL' : 'NOT NULL';
    $default = $columnDef['default'] !== null ? "DEFAULT " . (is_numeric($columnDef['default']) ? $columnDef['default'] : "'{$columnDef['default']}'") : '';
    $extra = $columnDef['extra'] ? $columnDef['extra'] : '';
    
    $after = $afterColumn ? "AFTER `{$afterColumn}`" : '';
    
    $parts = array_filter([$type, $null, $default, $extra]);
    $definition = implode(' ', $parts);
    
    return "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition} {$after}";
}

/**
 * Connect to live database
 */
function connectToLiveDatabase(): ?PDO {
    $liveConfigFile = __DIR__ . '/../config/database.live.php';
    
    if (!file_exists($liveConfigFile)) {
        return null;
    }
    
    $liveDbConfig = require $liveConfigFile;
    
    if (!is_array($liveDbConfig)) {
        return null;
    }
    
    // Support both naming conventions
    $host = $liveDbConfig['host'] ?? $liveDbConfig['hostname'] ?? 'localhost';
    $database = $liveDbConfig['database'] ?? $liveDbConfig['name'] ?? $liveDbConfig['dbname'] ?? '';
    $username = $liveDbConfig['username'] ?? $liveDbConfig['user'] ?? '';
    $password = $liveDbConfig['password'] ?? $liveDbConfig['pass'] ?? '';
    
    if (empty($database) || empty($username)) {
        return null;
    }
    
    try {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $host,
            $database
        );
        
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$quiet = in_array('--quiet', $argv);
$specificTable = null;

// Check for specific table option
foreach ($argv as $arg) {
    if (strpos($arg, '--table=') === 0) {
        $specificTable = substr($arg, 8);
    }
}

// Log file
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/schema-sync.log';

if (!$quiet) {
    echo "\n" . Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::BLUE . "  Automatic Schema Sync to Live" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
}

try {
    // Connect to local database
    $localDb = getDB();
    $localDbName = $localDb->query("SELECT DATABASE()")->fetchColumn();
    logMessage("Starting schema sync from local: $localDbName", $logFile);
    
    // Connect to live database
    $liveDb = connectToLiveDatabase();
    if (!$liveDb) {
        $error = "Failed to connect to live database. Check config/database.live.php";
        printError($error, $quiet);
        logMessage("ERROR: $error", $logFile);
        exit(1);
    }
    $liveDbName = $liveDb->query("SELECT DATABASE()")->fetchColumn();
    logMessage("Connected to live database: $liveDbName", $logFile);
    
    // Get list of tables to check
    $tablesToCheck = [];
    
    if ($specificTable) {
        // Check if table exists in local
        $exists = $localDb->query("SHOW TABLES LIKE '{$specificTable}'")->fetch();
        if (!$exists) {
            $error = "Table '{$specificTable}' does not exist in local database!";
            printError($error, $quiet);
            logMessage("ERROR: $error", $logFile);
            exit(1);
        }
        $tablesToCheck[] = $specificTable;
    } else {
        // Get all tables from local database
        $stmt = $localDb->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tablesToCheck[] = $row[0];
        }
    }
    
    $totalChanges = 0;
    $changesByTable = [];
    
    foreach ($tablesToCheck as $table) {
        // Check if table exists in live
        $existsInLive = $liveDb->query("SHOW TABLES LIKE '{$table}'")->fetch();
        if (!$existsInLive) {
            logMessage("Table '{$table}' does not exist in live database, skipping...", $logFile);
            continue;
        }
        
        // Get columns from both databases
        $localColumns = getTableColumns($localDb, $table);
        $liveColumns = getTableColumns($liveDb, $table);
        
        // Find missing columns
        $missingColumns = array_diff_key($localColumns, $liveColumns);
        
        if (empty($missingColumns)) {
            continue; // No changes needed
        }
        
        logMessage("Table '{$table}': Found " . count($missingColumns) . " missing column(s)", $logFile);
        
        $tableChanges = [];
        
        foreach ($missingColumns as $column => $columnDef) {
            // Determine position (after which column)
            $localColumnList = array_keys($localColumns);
            $columnIndex = array_search($column, $localColumnList);
            
            $afterColumn = null;
            if ($columnIndex > 0) {
                $afterColumn = $localColumnList[$columnIndex - 1];
            }
            
            $alterStatement = generateAlterStatement($table, $column, $columnDef, $afterColumn);
            
            $tableChanges[] = [
                'column' => $column,
                'statement' => $alterStatement,
            ];
        }
        
        // Apply changes
        foreach ($tableChanges as $change) {
            try {
                $liveDb->exec($change['statement']);
                $message = "Added column '{$change['column']}' to table '{$table}'";
                printSuccess($message, $quiet);
                logMessage($message, $logFile);
                $totalChanges++;
            } catch (PDOException $e) {
                // If column already exists (race condition), that's okay
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    logMessage("Column '{$change['column']}' in table '{$table}' already exists, skipping...", $logFile);
                } else {
                    $error = "Failed to add column '{$change['column']}' to table '{$table}': " . $e->getMessage();
                    printError($error, $quiet);
                    logMessage("ERROR: $error", $logFile);
                }
            }
        }
        
        if (!empty($tableChanges)) {
            $changesByTable[$table] = count($tableChanges);
        }
    }
    
    // Summary
    if ($totalChanges === 0) {
        $message = "Schema is up to date! No changes needed.";
        printSuccess($message, $quiet);
        logMessage($message, $logFile);
    } else {
        $message = "Applied {$totalChanges} change(s) across " . count($changesByTable) . " table(s)";
        printSuccess($message, $quiet);
        logMessage($message, $logFile);
        
        foreach ($changesByTable as $table => $count) {
            $detail = "  - {$table}: {$count} column(s) added";
            if (!$quiet) echo $detail . "\n";
            logMessage($detail, $logFile);
        }
    }
    
    logMessage("Schema sync completed successfully", $logFile);
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    printError($error, $quiet);
    logMessage("ERROR: $error", $logFile);
    logMessage("Stack trace: " . $e->getTraceAsString(), $logFile);
    exit(1);
}

exit(0);

