<?php
/**
 * Sync Database Schema to Live (cPanel)
 * 
 * Automatically adds missing columns from local database to live cPanel database
 * 
 * Usage:
 *   php bin/sync-schema-to-live.php                    # Dry run (show what will be changed)
 *   php bin/sync-schema-to-live.php --apply            # Actually apply changes
 *   php bin/sync-schema-to-live.php --table team_members  # Sync specific table only
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
    const BOLD = "\033[1m";
}

function printSuccess(string $message): void {
    echo Colors::GREEN . "✅ $message" . Colors::RESET . "\n";
}

function printError(string $message): void {
    echo Colors::RED . "❌ $message" . Colors::RESET . "\n";
}

function printWarning(string $message): void {
    echo Colors::YELLOW . "⚠️  $message" . Colors::RESET . "\n";
}

function printInfo(string $message): void {
    echo Colors::CYAN . "ℹ️  $message" . Colors::RESET . "\n";
}

function printHeader(string $message): void {
    echo "\n" . Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::BOLD . Colors::BLUE . "  $message" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
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
 * Get column position (after which column)
 */
function getColumnPosition(PDO $db, string $table, string $column): ?string {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM `{$table}`");
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }
        
        $index = array_search($column, $columns);
        if ($index === false || $index === 0) {
            return null;
        }
        
        return $columns[$index - 1];
    } catch (PDOException $e) {
        return null;
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
        printError("Live database config not found!");
        printInfo("Please create config/database.live.php from config/database.live.php.example");
        return null;
    }
    
    require $liveConfigFile;
    
    if (!isset($liveDbConfig)) {
        printError("Live database config not properly set!");
        return null;
    }
    
    try {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $liveDbConfig['host'],
            $liveDbConfig['name']
        );
        
        $pdo = new PDO($dsn, $liveDbConfig['user'], $liveDbConfig['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        printError("Failed to connect to live database: " . $e->getMessage());
        return null;
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$apply = in_array('--apply', $argv);
$dryRun = !$apply;
$specificTable = null;

// Check for specific table option
foreach ($argv as $arg) {
    if (strpos($arg, '--table=') === 0) {
        $specificTable = substr($arg, 8);
    }
}

printHeader("Sync Database Schema to Live (cPanel)");

try {
    // Connect to local database
    printInfo("Connecting to local database...");
    $localDb = getDB();
    $localDbName = $localDb->query("SELECT DATABASE()")->fetchColumn();
    printSuccess("Connected to local database: $localDbName");
    
    // Connect to live database
    printInfo("Connecting to live database...");
    $liveDb = connectToLiveDatabase();
    if (!$liveDb) {
        exit(1);
    }
    $liveDbName = $liveDb->query("SELECT DATABASE()")->fetchColumn();
    printSuccess("Connected to live database: $liveDbName");
    
    if ($dryRun) {
        printWarning("DRY RUN MODE - No changes will be made");
        printInfo("Use --apply flag to actually apply changes\n");
    }
    
    // Get list of tables to check
    $tablesToCheck = [];
    
    if ($specificTable) {
        // Check if table exists in local
        $exists = $localDb->query("SHOW TABLES LIKE '{$specificTable}'")->fetch();
        if (!$exists) {
            printError("Table '{$specificTable}' does not exist in local database!");
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
        printInfo("\nChecking table: {$table}");
        
        // Check if table exists in live
        $existsInLive = $liveDb->query("SHOW TABLES LIKE '{$table}'")->fetch();
        if (!$existsInLive) {
            printWarning("  Table '{$table}' does not exist in live database, skipping...");
            continue;
        }
        
        // Get columns from both databases
        $localColumns = getTableColumns($localDb, $table);
        $liveColumns = getTableColumns($liveDb, $table);
        
        // Find missing columns
        $missingColumns = array_diff_key($localColumns, $liveColumns);
        
        if (empty($missingColumns)) {
            printSuccess("  All columns are present, no changes needed");
            continue;
        }
        
        printInfo("  Found " . count($missingColumns) . " missing column(s):");
        
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
            
            echo "    - {$column} ({$columnDef['type']})\n";
            if ($dryRun) {
                echo "      SQL: {$alterStatement}\n";
            }
            
            $tableChanges[] = [
                'column' => $column,
                'statement' => $alterStatement,
            ];
        }
        
        if (!$dryRun && !empty($tableChanges)) {
            printInfo("  Applying changes to live database...");
            
            foreach ($tableChanges as $change) {
                try {
                    $liveDb->exec($change['statement']);
                    printSuccess("    Added column: {$change['column']}");
                } catch (PDOException $e) {
                    // If column already exists (race condition), that's okay
                    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                        printInfo("    Column '{$change['column']}' already exists, skipping...");
                    } else {
                        printError("    Failed to add column '{$change['column']}': " . $e->getMessage());
                    }
                }
            }
        }
        
        $changesByTable[$table] = $tableChanges;
        $totalChanges += count($tableChanges);
    }
    
    printHeader("Summary");
    
    if ($totalChanges === 0) {
        printSuccess("No schema changes needed! All tables are in sync.");
    } else {
        if ($dryRun) {
            printInfo("Would apply {$totalChanges} change(s) across " . count($changesByTable) . " table(s)");
            printInfo("Run with --apply flag to actually apply these changes");
        } else {
            printSuccess("Applied {$totalChanges} change(s) across " . count($changesByTable) . " table(s)");
            
            foreach ($changesByTable as $table => $changes) {
                if (!empty($changes)) {
                    echo "  - {$table}: " . count($changes) . " column(s) added\n";
                }
            }
        }
    }
    
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    echo "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);

