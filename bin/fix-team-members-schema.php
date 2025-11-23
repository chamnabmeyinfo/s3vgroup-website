<?php
/**
 * Fix Team Members Table Schema
 * 
 * Adds missing columns to team_members table that are used by TeamMemberRepository
 * 
 * Usage:
 *   php bin/fix-team-members-schema.php
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
 * Check if column exists in table
 */
function columnExists(PDO $db, string $table, string $column): bool {
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = :table 
            AND COLUMN_NAME = :column
        ");
        $stmt->execute([':table' => $table, ':column' => $column]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

printHeader("Fix Team Members Table Schema");

try {
    $db = getDB();
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    
    printInfo("Database: $dbName");
    
    // Check if table exists
    $tableExists = $db->query("SHOW TABLES LIKE 'team_members'")->fetch();
    if (!$tableExists) {
        printError("Table 'team_members' does not exist!");
        printInfo("Please run the initial schema migration first.");
        exit(1);
    }
    
    // Columns to add (in order)
    $columnsToAdd = [
        'department' => [
            'type' => 'VARCHAR(255)',
            'null' => true,
            'after' => 'title',
        ],
        'expertise' => [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'bio',
        ],
        'location' => [
            'type' => 'VARCHAR(255)',
            'null' => true,
            'after' => 'phone',
        ],
        'languages' => [
            'type' => 'VARCHAR(255)',
            'null' => true,
            'after' => 'location',
        ],
        'twitter' => [
            'type' => 'VARCHAR(500)',
            'null' => true,
            'after' => 'linkedin',
        ],
        'facebook' => [
            'type' => 'VARCHAR(500)',
            'null' => true,
            'after' => 'twitter',
        ],
        'instagram' => [
            'type' => 'VARCHAR(500)',
            'null' => true,
            'after' => 'facebook',
        ],
        'website' => [
            'type' => 'VARCHAR(500)',
            'null' => true,
            'after' => 'instagram',
        ],
        'github' => [
            'type' => 'VARCHAR(500)',
            'null' => true,
            'after' => 'website',
        ],
        'youtube' => [
            'type' => 'VARCHAR(500)',
            'null' => true,
            'after' => 'github',
        ],
        'telegram' => [
            'type' => 'VARCHAR(500)',
            'null' => true,
            'after' => 'youtube',
        ],
        'whatsapp' => [
            'type' => 'VARCHAR(100)',
            'null' => true,
            'after' => 'telegram',
        ],
    ];
    
    $added = 0;
    $skipped = 0;
    
    foreach ($columnsToAdd as $column => $config) {
        if (columnExists($db, 'team_members', $column)) {
            printInfo("Column '$column' already exists, skipping...");
            $skipped++;
            continue;
        }
        
        $nullClause = $config['null'] ? 'NULL' : 'NOT NULL';
        $afterClause = $config['after'] ? "AFTER `{$config['after']}`" : '';
        
        $sql = "ALTER TABLE `team_members` ADD COLUMN `{$column}` {$config['type']} {$nullClause} {$afterClause}";
        
        try {
            $db->exec($sql);
            printSuccess("Added column '$column'");
            $added++;
        } catch (PDOException $e) {
            // If column was added between check and execution, that's okay
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                printInfo("Column '$column' already exists (race condition), skipping...");
                $skipped++;
            } else {
                printError("Failed to add column '$column': " . $e->getMessage());
            }
        }
    }
    
    printHeader("Summary");
    
    if ($added > 0) {
        printSuccess("Added $added column(s) to team_members table");
    }
    
    if ($skipped > 0) {
        printInfo("Skipped $skipped column(s) (already exist)");
    }
    
    if ($added === 0 && $skipped === count($columnsToAdd)) {
        printInfo("All columns already exist. Schema is up to date!");
    }
    
    // Verify all columns exist
    printInfo("\nVerifying schema...");
    $missing = [];
    foreach (array_keys($columnsToAdd) as $column) {
        if (!columnExists($db, 'team_members', $column)) {
            $missing[] = $column;
        }
    }
    
    if (empty($missing)) {
        printSuccess("All required columns are present!");
    } else {
        printError("Missing columns: " . implode(', ', $missing));
        exit(1);
    }
    
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

exit(0);

