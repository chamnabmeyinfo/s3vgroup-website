<?php
/**
 * Verify Database Schema
 * 
 * Checks all tables and columns to ensure they match the expected schema
 * 
 * Usage:
 *   php bin/verify-database-schema.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

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

/**
 * Get all columns in a table
 */
function getTableColumns(PDO $db, string $table): array {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM `{$table}`");
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }
        return $columns;
    } catch (PDOException $e) {
        return [];
    }
}

// Expected schema for critical tables
$expectedSchema = [
    'team_members' => [
        'id', 'name', 'title', 'department', 'bio', 'expertise', 'photo', 
        'email', 'phone', 'location', 'languages', 'linkedin', 'twitter', 
        'facebook', 'instagram', 'website', 'github', 'youtube', 'telegram', 
        'whatsapp', 'priority', 'status', 'createdAt', 'updatedAt'
    ],
    'products' => [
        'id', 'name', 'slug', 'sku', 'summary', 'description', 'specs', 
        'heroImage', 'price', 'status', 'highlights', 'categoryId', 
        'createdAt', 'updatedAt'
    ],
    'categories' => [
        'id', 'name', 'slug', 'description', 'icon', 'priority', 
        'createdAt', 'updatedAt'
    ],
    'sliders' => [
        'id', 'title', 'subtitle', 'description', 'image_url', 'link_url', 
        'link_text', 'button_color', 'priority', 'status', 'createdAt', 'updatedAt'
    ],
    'ceo_message' => [
        'id', 'title', 'message', 'photo', 'name', 'position', 'signature', 
        'displayOrder', 'status', 'createdAt', 'updatedAt'
    ],
    'company_story' => [
        'id', 'title', 'subtitle', 'heroImage', 'introduction', 'history', 
        'mission', 'vision', 'values', 'milestones', 'achievements', 
        'status', 'createdAt', 'updatedAt'
    ],
];

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

printHeader("Verify Database Schema");

try {
    $db = getDB();
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    
    printInfo("Database: $dbName\n");
    
    $allGood = true;
    $issues = [];
    
    foreach ($expectedSchema as $table => $expectedColumns) {
        // Check if table exists
        $tableExists = $db->query("SHOW TABLES LIKE '{$table}'")->fetch();
        if (!$tableExists) {
            printError("Table '{$table}' does not exist!");
            $allGood = false;
            $issues[] = "Missing table: {$table}";
            continue;
        }
        
        printInfo("Checking table: {$table}");
        
        // Get actual columns
        $actualColumns = getTableColumns($db, $table);
        
        // Check for missing columns
        $missing = array_diff($expectedColumns, $actualColumns);
        if (!empty($missing)) {
            printError("  Missing columns: " . implode(', ', $missing));
            $allGood = false;
            $issues[] = "Table {$table} missing columns: " . implode(', ', $missing);
        } else {
            printSuccess("  All required columns present");
        }
        
        // Check for extra columns (informational only)
        $extra = array_diff($actualColumns, $expectedColumns);
        if (!empty($extra)) {
            printInfo("  Extra columns (not in expected schema): " . implode(', ', $extra));
        }
    }
    
    printHeader("Summary");
    
    if ($allGood) {
        printSuccess("All tables and columns are correct!");
    } else {
        printError("Found " . count($issues) . " issue(s):");
        foreach ($issues as $issue) {
            echo "  - $issue\n";
        }
        echo "\n";
        printInfo("Run 'php bin/fix-team-members-schema.php' to fix team_members table");
        printInfo("Or update your database schema manually");
    }
    
} catch (Exception $e) {
    printError("Error: " . $e->getMessage());
    exit(1);
}

exit($allGood ? 0 : 1);

