<?php
/**
 * Automatic Database Synchronization
 * 
 * Automatically checks if live database (cPanel) has newer updates
 * and syncs to localhost if needed.
 * 
 * Usage:
 *   php bin/auto-sync-database.php
 *   php bin/auto-sync-database.php --force (force sync even if same)
 *   php bin/auto-sync-database.php --check-only (only check, don't sync)
 * 
 * This script:
 * 1. Checks live database last update time
 * 2. Compares with local database last update time
 * 3. If live is newer, automatically exports and imports
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

// Colors for terminal output
class Colors {
    const RESET = "\033[0m";
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const MAGENTA = "\033[35m";
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
    echo Colors::BLUE . "  $message" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
}

/**
 * Get database last update timestamp
 */
function getDatabaseLastUpdate(PDO $db): ?int {
    try {
        // Check multiple tables for last update time
        $tables = ['products', 'categories', 'team_members', 'testimonials', 'quote_requests', 'pages', 'sliders'];
        $maxTimestamp = 0;
        
        foreach ($tables as $table) {
            try {
                // Check if table exists
                $exists = $db->query("SHOW TABLES LIKE '$table'")->fetch();
                if (!$exists) {
                    continue;
                }
                
                // Get max updatedAt timestamp
                $result = $db->query("SELECT MAX(updatedAt) as lastUpdate FROM `$table`")->fetch(PDO::FETCH_ASSOC);
                if ($result && $result['lastUpdate']) {
                    $timestamp = strtotime($result['lastUpdate']);
                    if ($timestamp > $maxTimestamp) {
                        $maxTimestamp = $timestamp;
                    }
                }
            } catch (PDOException $e) {
                // Table might not exist or have updatedAt column, skip
                continue;
            }
        }
        
        return $maxTimestamp > 0 ? $maxTimestamp : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get database info (name, table count, row count)
 */
function getDatabaseInfo(PDO $db): array {
    try {
        $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $totalRows = 0;
        foreach ($tables as $table) {
            try {
                $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                $totalRows += (int)$count;
            } catch (PDOException $e) {
                // Skip if error
            }
        }
        
        return [
            'name' => $dbName,
            'tables' => count($tables),
            'rows' => $totalRows,
            'lastUpdate' => getDatabaseLastUpdate($db),
        ];
    } catch (Exception $e) {
        return [
            'name' => 'Unknown',
            'tables' => 0,
            'rows' => 0,
            'lastUpdate' => null,
        ];
    }
}

/**
 * Connect to live database (cPanel)
 * Uses environment variables or config file
 */
function connectToLiveDatabase(): ?PDO {
    try {
        // Try to get live database config from environment or separate config
        $liveConfigFile = __DIR__ . '/../config/database.live.php';
        
        if (file_exists($liveConfigFile)) {
            $config = require $liveConfigFile;
            
            if (is_array($config)) {
                $host = $config['host'] ?? 'localhost';
                $dbname = $config['database'] ?? '';
                $username = $config['username'] ?? '';
                $password = $config['password'] ?? '';
                $charset = $config['charset'] ?? 'utf8mb4';
                
                $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                return new PDO($dsn, $username, $password, $options);
            }
        }
        
        // Try environment variables
        $host = getenv('LIVE_DB_HOST') ?: getenv('DB_HOST');
        $dbname = getenv('LIVE_DB_DATABASE') ?: getenv('DB_DATABASE');
        $username = getenv('LIVE_DB_USERNAME') ?: getenv('DB_USERNAME');
        $password = getenv('LIVE_DB_PASSWORD') ?: getenv('DB_PASSWORD');
        
        if ($host && $dbname && $username) {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            return new PDO($dsn, $username, $password, $options);
        }
        
        return null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Export database to SQL file
 */
function exportDatabase(PDO $db, string $outputFile): bool {
    try {
        $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            printWarning("No tables found in database");
            return false;
        }
        
        $handle = fopen($outputFile, 'w');
        if (!$handle) {
            printError("Cannot create output file: $outputFile");
            return false;
        }
        
        fwrite($handle, "-- Database Export\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: $dbName\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET AUTOCOMMIT=0;\n");
        fwrite($handle, "START TRANSACTION;\n\n");
        
        foreach ($tables as $table) {
            $createTable = $db->query("SHOW CREATE TABLE `$table`")->fetch();
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($handle, $createTable['Create Table'] . ";\n\n");
            
            $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                $chunks = array_chunk($rows, 100);
                foreach ($chunks as $chunk) {
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            $rowValues[] = $value === null ? 'NULL' : $db->quote($value);
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    fwrite($handle, "INSERT INTO `$table` ($columnList) VALUES\n" . implode(",\n", $values) . ";\n\n");
                }
            }
        }
        
        fwrite($handle, "COMMIT;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
        
        return true;
    } catch (Exception $e) {
        printError("Export failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Import database from SQL file
 */
function importDatabase(PDO $db, string $inputFile): bool {
    try {
        if (!file_exists($inputFile)) {
            printError("Input file not found: $inputFile");
            return false;
        }
        
        $sql = file_get_contents($inputFile);
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        $statements = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $current .= $char;
            
            if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i-1] !== '\\')) {
                if (!$inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = '';
                }
            }
            
            if (!$inQuotes && $char === ';') {
                $statement = trim($current);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $current = '';
            }
        }
        
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }
        
        $executed = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strtoupper(substr($statement, 0, 2)) === '--') {
                continue;
            }
            
            try {
                $db->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    printWarning("SQL Error: " . $e->getMessage());
                }
            }
        }
        
        printSuccess("Imported $executed statement(s)");
        return true;
    } catch (Exception $e) {
        printError("Import failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Main auto-sync function
 */
function autoSync(bool $force = false, bool $checkOnly = false): bool {
    printHeader("Automatic Database Synchronization");
    
    // Connect to local database
    printInfo("Connecting to local database...");
    try {
        require_once __DIR__ . '/../config/database.php';
        $localDb = getDB();
        $localInfo = getDatabaseInfo($localDb);
        printSuccess("Connected to local database: " . $localInfo['name']);
    } catch (Exception $e) {
        printError("Cannot connect to local database: " . $e->getMessage());
        return false;
    }
    
    // Connect to live database
    printInfo("Connecting to live database (cPanel)...");
    $liveDb = connectToLiveDatabase();
    
    if (!$liveDb) {
        printWarning("Cannot connect to live database directly.");
        printInfo("This is normal if remote MySQL access is not enabled.");
        printInfo("Alternative: Use phpMyAdmin to export, then run:");
        printInfo("  php bin/sync-database.php import local database-live.sql --force");
        return false;
    }
    
    try {
        $liveInfo = getDatabaseInfo($liveDb);
        printSuccess("Connected to live database: " . $liveInfo['name']);
    } catch (Exception $e) {
        printError("Cannot get live database info: " . $e->getMessage());
        return false;
    }
    
    // Compare databases
    printHeader("Comparing Databases");
    
    echo "Local Database:\n";
    echo "  Name: " . $localInfo['name'] . "\n";
    echo "  Tables: " . $localInfo['tables'] . "\n";
    echo "  Rows: " . number_format($localInfo['rows']) . "\n";
    if ($localInfo['lastUpdate']) {
        echo "  Last Update: " . date('Y-m-d H:i:s', $localInfo['lastUpdate']) . "\n";
    } else {
        echo "  Last Update: Unknown\n";
    }
    
    echo "\nLive Database:\n";
    echo "  Name: " . $liveInfo['name'] . "\n";
    echo "  Tables: " . $liveInfo['tables'] . "\n";
    echo "  Rows: " . number_format($liveInfo['rows']) . "\n";
    if ($liveInfo['lastUpdate']) {
        echo "  Last Update: " . date('Y-m-d H:i:s', $liveInfo['lastUpdate']) . "\n";
    } else {
        echo "  Last Update: Unknown\n";
    }
    
    // Determine if sync is needed
    $needsSync = false;
    $reason = '';
    
    if ($force) {
        $needsSync = true;
        $reason = 'Force flag enabled';
    } elseif ($localInfo['lastUpdate'] && $liveInfo['lastUpdate']) {
        if ($liveInfo['lastUpdate'] > $localInfo['lastUpdate']) {
            $needsSync = true;
            $diff = $liveInfo['lastUpdate'] - $localInfo['lastUpdate'];
            $reason = "Live database is newer (by " . round($diff / 60) . " minutes)";
        } elseif ($localInfo['lastUpdate'] > $liveInfo['lastUpdate']) {
            $needsSync = false;
            $diff = $localInfo['lastUpdate'] - $liveInfo['lastUpdate'];
            $reason = "Local database is newer (by " . round($diff / 60) . " minutes)";
        } else {
            $needsSync = false;
            $reason = "Databases are in sync";
        }
    } elseif ($liveInfo['tables'] > $localInfo['tables']) {
        $needsSync = true;
        $reason = "Live database has more tables";
    } elseif ($liveInfo['rows'] > $localInfo['rows']) {
        $needsSync = true;
        $reason = "Live database has more data";
    } else {
        $needsSync = false;
        $reason = "No significant differences detected";
    }
    
    echo "\n" . Colors::MAGENTA . "Decision: " . $reason . Colors::RESET . "\n";
    
    if ($checkOnly) {
        printInfo("Check-only mode: No sync performed");
        return $needsSync;
    }
    
    if (!$needsSync) {
        printSuccess("Databases are in sync. No action needed.");
        return true;
    }
    
    // Perform sync
    printHeader("Starting Automatic Sync");
    printWarning("This will overwrite your local database with live data!");
    
    $tempFile = sys_get_temp_dir() . '/s3vgroup-auto-sync-' . time() . '.sql';
    
    // Export from live
    printInfo("Step 1: Exporting from live database...");
    if (!exportDatabase($liveDb, $tempFile)) {
        printError("Export failed");
        return false;
    }
    $fileSize = filesize($tempFile);
    printSuccess("Exported " . number_format($fileSize) . " bytes");
    
    // Import to local
    printInfo("Step 2: Importing to local database...");
    if (!importDatabase($localDb, $tempFile)) {
        printError("Import failed");
        @unlink($tempFile);
        return false;
    }
    
    // Cleanup
    @unlink($tempFile);
    
    // Verify
    printInfo("Step 3: Verifying sync...");
    $newLocalInfo = getDatabaseInfo($localDb);
    
    printHeader("Sync Complete!");
    echo "Local Database (after sync):\n";
    echo "  Tables: " . $newLocalInfo['tables'] . "\n";
    echo "  Rows: " . number_format($newLocalInfo['rows']) . "\n";
    if ($newLocalInfo['lastUpdate']) {
        echo "  Last Update: " . date('Y-m-d H:i:s', $newLocalInfo['lastUpdate']) . "\n";
    }
    
    printSuccess("Database synchronized successfully!");
    return true;
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$force = in_array('--force', $argv);
$checkOnly = in_array('--check-only', $argv);

$result = autoSync($force, $checkOnly);
exit($result ? 0 : 1);

