<?php
/**
 * Sync Local Database to Live (s3vgroup.com)
 * 
 * Exports the local database and imports it to the live cPanel database.
 * This will update the live website with all migrated WordPress content.
 * 
 * Usage:
 *   php bin/sync-database-to-live.php              # Dry run (preview)
 *   php bin/sync-database-to-live.php --apply       # Actually sync
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

// Load live database config
$liveConfigFile = __DIR__ . '/../config/database.live.php';
if (!file_exists($liveConfigFile)) {
    die("❌ Error: config/database.live.php not found!\n   Please configure your live database credentials first.\n");
}

$liveConfig = require $liveConfigFile;

// Colors for output
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

function printWarning(string $message): void {
    echo Colors::YELLOW . "⚠️  $message" . Colors::RESET . "\n";
}

function printHeader(string $message): void {
    echo "\n" . Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::BOLD . Colors::BLUE . "  $message" . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════" . Colors::RESET . "\n\n";
}

if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$apply = in_array('--apply', $argv);
$dryRun = !$apply;

printHeader("Sync Local Database to Live (s3vgroup.com)");

if ($dryRun) {
    printWarning("DRY RUN MODE - Database will not be synced");
    printInfo("Use --apply flag to actually sync to live database\n");
} else {
    printWarning("APPLY MODE - Live database will be updated!");
    printInfo("Press Ctrl+C within 5 seconds to cancel...\n");
    sleep(5);
}

try {
    // Connect to local database
    $localDb = getDB();
    printSuccess("Connected to local database");
    
    // Connect to live database
    $liveDb = new PDO(
        "mysql:host={$liveConfig['host']};dbname={$liveConfig['database']};charset=utf8mb4",
        $liveConfig['username'],
        $liveConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    printSuccess("Connected to live database: {$liveConfig['database']}\n");
    
    // Get table counts
    printHeader("Database Comparison");
    
    $tables = ['categories', 'products', 'product_media', 'team_members', 'sliders', 'testimonials'];
    
    foreach ($tables as $table) {
        try {
            $localCount = $localDb->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $liveCount = $liveDb->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            
            $diff = $localCount - $liveCount;
            if ($diff > 0) {
                printInfo("$table: Local=$localCount, Live=$liveCount (+$diff new)");
            } elseif ($diff < 0) {
                printWarning("$table: Local=$localCount, Live=$liveCount ($diff fewer)");
            } else {
                printInfo("$table: Local=$localCount, Live=$liveCount (same)");
            }
        } catch (PDOException $e) {
            printError("$table: Error - " . $e->getMessage());
        }
    }
    
    if ($dryRun) {
        printInfo("\nDRY RUN COMPLETE - No changes made");
        printInfo("Run with --apply flag to sync database to live");
        exit(0);
    }
    
    // Export local database
    printHeader("Exporting Local Database");
    
    $exportFile = sys_get_temp_dir() . '/s3vgroup_export_' . date('YmdHis') . '.sql';
    $mysqlPath = 'C:/xampp/mysql/bin/mysql';
    $mysqldumpPath = 'C:/xampp/mysql/bin/mysqldump';
    
    // Get local DB config
    require_once __DIR__ . '/../config/database.php';
    $localConfig = [
        'host' => '127.0.0.1',
        'database' => getDB()->query('SELECT DATABASE()')->fetchColumn(),
        'username' => 'root',
        'password' => '',
    ];
    
    // Export command
    $exportCmd = sprintf(
        '"%s" -u %s %s %s > "%s"',
        $mysqldumpPath,
        escapeshellarg($localConfig['username']),
        $localConfig['password'] ? '-p' . escapeshellarg($localConfig['password']) : '',
        escapeshellarg($localConfig['database']),
        $exportFile
    );
    
    printInfo("Exporting database to: $exportFile");
    exec($exportCmd . ' 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0 || !file_exists($exportFile) || filesize($exportFile) < 100) {
        printError("Failed to export database");
        if (!empty($output)) {
            echo implode("\n", $output) . "\n";
        }
        exit(1);
    }
    
    $fileSize = filesize($exportFile);
    printSuccess("Database exported: " . number_format($fileSize / 1024, 2) . " KB");
    
    // Import to live database
    printHeader("Importing to Live Database");
    
    printWarning("This will REPLACE all data in the live database!");
    printInfo("Importing SQL file to live database...");
    
    // Read SQL file in chunks and execute
    $sql = file_get_contents($exportFile);
    
    // Split by semicolons (basic approach)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($stmt) => !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt)
    );
    
    $imported = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $liveDb->exec($statement);
            $imported++;
        } catch (PDOException $e) {
            // Ignore "table already exists" errors for CREATE TABLE IF NOT EXISTS
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors++;
                if ($errors <= 5) {
                    printError("SQL Error: " . substr($e->getMessage(), 0, 100));
                }
            }
        }
    }
    
    printSuccess("Imported $imported SQL statements");
    if ($errors > 0) {
        printWarning("$errors errors encountered (may be expected)");
    }
    
    // Clean up
    if (file_exists($exportFile)) {
        unlink($exportFile);
    }
    
    // Verify
    printHeader("Verification");
    
    foreach ($tables as $table) {
        try {
            $localCount = $localDb->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $liveCount = $liveDb->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            
            if ($localCount == $liveCount) {
                printSuccess("$table: Synced ($liveCount records)");
            } else {
                printWarning("$table: Local=$localCount, Live=$liveCount (mismatch)");
            }
        } catch (PDOException $e) {
            printError("$table: Error - " . $e->getMessage());
        }
    }
    
    printHeader("Sync Complete");
    printSuccess("Database synced to live successfully!");
    printInfo("Visit https://s3vgroup.com to see the updated content");
    
} catch (PDOException $e) {
    printError("Database error: " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

exit(0);

