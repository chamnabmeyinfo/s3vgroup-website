<?php
/**
 * Database Synchronization Tool
 * 
 * Sync database between localhost and cPanel (live server)
 * 
 * Usage:
 *   php bin/sync-database.php export [local|live] [output.sql]
 *   php bin/sync-database.php import [local|live] [input.sql]
 *   php bin/sync-database.php sync [direction] [--force]
 * 
 * Examples:
 *   php bin/sync-database.php export live database-live.sql
 *   php bin/sync-database.php import local database-live.sql
 *   php bin/sync-database.php sync live-to-local
 *   php bin/sync-database.php sync local-to-live --force
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
 * Export database to SQL file
 */
function exportDatabase(string $target, string $outputFile): bool {
    try {
        printHeader("Exporting Database: $target");
        
        // Get database connection
        $db = getDB();
        $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
        
        printInfo("Database: $dbName");
        printInfo("Output file: $outputFile");
        
        // Get all tables
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            printWarning("No tables found in database");
            return false;
        }
        
        printInfo("Found " . count($tables) . " table(s)");
        
        // Open output file
        $handle = fopen($outputFile, 'w');
        if (!$handle) {
            printError("Cannot create output file: $outputFile");
            return false;
        }
        
        // Write header
        fwrite($handle, "-- Database Export\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: $dbName\n");
        fwrite($handle, "-- Target: $target\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET AUTOCOMMIT=0;\n");
        fwrite($handle, "START TRANSACTION;\n\n");
        
        $totalRows = 0;
        
        // Export each table
        foreach ($tables as $table) {
            printInfo("Exporting table: $table");
            
            // Get table structure
            $createTable = $db->query("SHOW CREATE TABLE `$table`")->fetch();
            fwrite($handle, "-- Table structure for `$table`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($handle, $createTable['Create Table'] . ";\n\n");
            
            // Get table data
            $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                fwrite($handle, "-- Data for table `$table`\n");
                
                // Get column names
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                // Insert data in chunks
                $chunkSize = 100;
                $chunks = array_chunk($rows, $chunkSize);
                
                foreach ($chunks as $chunk) {
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = $db->quote($value);
                            }
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    
                    $sql = "INSERT INTO `$table` ($columnList) VALUES\n" . implode(",\n", $values) . ";\n\n";
                    fwrite($handle, $sql);
                }
                
                $totalRows += count($rows);
                printSuccess("Exported " . count($rows) . " row(s) from $table");
            } else {
                printInfo("Table $table is empty");
            }
        }
        
        // Write footer
        fwrite($handle, "COMMIT;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        
        fclose($handle);
        
        $fileSize = filesize($outputFile);
        printSuccess("Export complete!");
        printInfo("Total tables: " . count($tables));
        printInfo("Total rows: $totalRows");
        printInfo("File size: " . number_format($fileSize) . " bytes (" . number_format($fileSize / 1024, 2) . " KB)");
        
        return true;
        
    } catch (Exception $e) {
        printError("Export failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Import database from SQL file
 */
function importDatabase(string $target, string $inputFile, bool $force = false): bool {
    try {
        printHeader("Importing Database: $target");
        
        if (!file_exists($inputFile)) {
            printError("Input file not found: $inputFile");
            return false;
        }
        
        $db = getDB();
        $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
        
        printInfo("Database: $dbName");
        printInfo("Input file: $inputFile");
        printInfo("File size: " . number_format(filesize($inputFile)) . " bytes");
        
        // Check existing tables
        $existingTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($existingTables) && !$force) {
            printWarning("Database already contains " . count($existingTables) . " table(s)");
            printWarning("Use --force flag to overwrite existing data");
            return false;
        }
        
        // Read SQL file
        $sql = file_get_contents($inputFile);
        
        if ($sql === false) {
            printError("Cannot read SQL file");
            return false;
        }
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split into statements
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
        
        printInfo("Found " . count($statements) . " SQL statement(s)");
        
        // Execute statements
        $executed = 0;
        $errors = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            if (empty($statement) || 
                strtoupper(substr($statement, 0, 2)) === '--' ||
                strtoupper(substr($statement, 0, 2)) === '/*') {
                continue;
            }
            
            try {
                $db->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Ignore "table already exists" errors if force is enabled
                if ($force && (
                    strpos($e->getMessage(), 'already exists') !== false ||
                    strpos($e->getMessage(), 'Duplicate') !== false
                )) {
                    continue;
                }
                $errors++;
                printWarning("SQL Error: " . $e->getMessage());
            }
        }
        
        printSuccess("Import complete!");
        printInfo("Executed: $executed statement(s)");
        if ($errors > 0) {
            printWarning("Errors: $errors");
        }
        
        // Verify tables
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        printInfo("Total tables in database: " . count($tables));
        
        return true;
        
    } catch (Exception $e) {
        printError("Import failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Sync database between local and live
 */
function syncDatabase(string $direction, bool $force = false): bool {
    $parts = explode('-to-', $direction);
    
    if (count($parts) !== 2) {
        printError("Invalid direction format. Use: local-to-live or live-to-local");
        return false;
    }
    
    $source = $parts[0];
    $target = $parts[1];
    
    if (!in_array($source, ['local', 'live']) || !in_array($target, ['local', 'live'])) {
        printError("Invalid direction. Use: local-to-live or live-to-local");
        return false;
    }
    
    printHeader("Database Sync: $source → $target");
    printWarning("This will export from $source and import to $target");
    
    if (!$force) {
        printWarning("Use --force flag to proceed");
        return false;
    }
    
    // Generate temp file
    $tempFile = sys_get_temp_dir() . '/s3vgroup-sync-' . time() . '.sql';
    
    // Export from source
    printInfo("Step 1: Exporting from $source...");
    if (!exportDatabase($source, $tempFile)) {
        printError("Export failed");
        return false;
    }
    
    // Import to target
    printInfo("Step 2: Importing to $target...");
    if (!importDatabase($target, $tempFile, true)) {
        printError("Import failed");
        @unlink($tempFile);
        return false;
    }
    
    // Cleanup
    @unlink($tempFile);
    
    printSuccess("Sync complete!");
    return true;
}

// Main script
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$command = $argv[1] ?? null;
$target = $argv[2] ?? null;
$file = $argv[3] ?? null;
$force = in_array('--force', $argv);

if (!$command) {
    echo "Database Synchronization Tool\n\n";
    echo "Usage:\n";
    echo "  php bin/sync-database.php export [local|live] [output.sql]\n";
    echo "  php bin/sync-database.php import [local|live] [input.sql]\n";
    echo "  php bin/sync-database.php sync [local-to-live|live-to-local] [--force]\n\n";
    echo "Examples:\n";
    echo "  php bin/sync-database.php export live database-live.sql\n";
    echo "  php bin/sync-database.php import local database-live.sql\n";
    echo "  php bin/sync-database.php sync live-to-local --force\n";
    exit(1);
}

switch ($command) {
    case 'export':
        if (!$target || !$file) {
            printError("Usage: php bin/sync-database.php export [local|live] [output.sql]");
            exit(1);
        }
        exit(exportDatabase($target, $file) ? 0 : 1);
        
    case 'import':
        if (!$target || !$file) {
            printError("Usage: php bin/sync-database.php import [local|live] [input.sql]");
            exit(1);
        }
        exit(importDatabase($target, $file, $force) ? 0 : 1);
        
    case 'sync':
        if (!$target) {
            printError("Usage: php bin/sync-database.php sync [local-to-live|live-to-local] [--force]");
            exit(1);
        }
        exit(syncDatabase($target, $force) ? 0 : 1);
        
    default:
        printError("Unknown command: $command");
        exit(1);
}

