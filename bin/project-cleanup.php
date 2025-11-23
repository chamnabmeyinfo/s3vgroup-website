<?php
/**
 * Project Cleanup Script
 * 
 * Removes temporary fix files, duplicate documentation, and one-time SQL fixes
 * that are no longer needed after issues have been resolved.
 * 
 * Usage:
 *   php bin/project-cleanup.php              # Dry run (show what will be deleted)
 *   php bin/project-cleanup.php --apply      # Actually delete files
 */

declare(strict_types=1);

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

if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

$apply = in_array('--apply', $argv);
$dryRun = !$apply;

$projectRoot = __DIR__ . '/..';

printHeader("Project Cleanup");

if ($dryRun) {
    printWarning("DRY RUN MODE - No files will be deleted");
    printInfo("Use --apply flag to actually delete files\n");
} else {
    printWarning("APPLY MODE - Files will be permanently deleted!");
    printInfo("Press Ctrl+C within 5 seconds to cancel...\n");
    sleep(5);
}

// Files to remove
$filesToRemove = [
    // One-time fix documentation (issues already resolved)
    'FIX-DEPARTMENT-COLUMN.md',
    'FIX-CONNECTION-ISSUE.md',
    'FIX-LIVE-CONNECTION.md',
    'QUICK-FIX-TEAM-MEMBERS.md',
    'CREATE-LIVE-CONFIG.md',
    
    // Duplicate/redundant documentation
    'SETUP-AUTO-SYNC-NOW.md',
    'QUICK-START-AUTO-SYNC.md',
    
    // One-time SQL fix files (already applied to live DB)
    'sql/fix-team-members-simple.sql',
    'sql/fix-team-members-columns.sql',
    'sql/fix-team-members-columns-mysql.sql',
];

$removed = [];
$notFound = [];
$errors = [];

foreach ($filesToRemove as $file) {
    $fullPath = $projectRoot . '/' . $file;
    
    if (!file_exists($fullPath)) {
        $notFound[] = $file;
        continue;
    }
    
    if ($dryRun) {
        printInfo("Would delete: $file");
        $removed[] = $file;
    } else {
        try {
            if (unlink($fullPath)) {
                printSuccess("Deleted: $file");
                $removed[] = $file;
            } else {
                $errors[] = $file;
                printError("Failed to delete: $file");
            }
        } catch (Exception $e) {
            $errors[] = $file;
            printError("Error deleting $file: " . $e->getMessage());
        }
    }
}

printHeader("Summary");

if (!empty($notFound)) {
    printInfo(count($notFound) . " file(s) not found (may have been deleted already):");
    foreach ($notFound as $file) {
        echo "  - $file\n";
    }
    echo "\n";
}

if ($dryRun) {
    printInfo("Would delete " . count($removed) . " file(s)");
    printInfo("Run with --apply flag to actually delete these files");
} else {
    if (count($removed) > 0) {
        printSuccess("Deleted " . count($removed) . " file(s)");
    }
    
    if (count($errors) > 0) {
        printError("Failed to delete " . count($errors) . " file(s)");
    }
    
    if (count($removed) === 0 && count($errors) === 0) {
        printInfo("No files to delete (all already removed or not found)");
    }
}

// Show what was removed
if (!empty($removed)) {
    echo "\n" . Colors::CYAN . "Files " . ($dryRun ? "that would be " : "") . "removed:\n" . Colors::RESET;
    foreach ($removed as $file) {
        echo "  - $file\n";
    }
}

// Essential files check
printHeader("Essential Files Check");

$essentialFiles = [
    'README.md',
    'LOCAL-SETUP.md',
    'LIVE-SETUP-GUIDE.md',
    'AUTO-SCHEMA-SYNC-SETUP.md',
    'DATABASE-SYNC-GUIDE.md',
    'DATABASE-MANAGER-GUIDE.md',
    'SCHEMA-SYNC-GUIDE.md',
    'sql/schema.sql',
    'sql/site_options.sql',
    'sql/sample_data.sql',
    'bin/auto-sync-schema.php',
    'bin/db-manager.php',
    'config/database.live.php.example',
];

$missing = [];
foreach ($essentialFiles as $file) {
    $fullPath = $projectRoot . '/' . $file;
    if (!file_exists($fullPath)) {
        $missing[] = $file;
        printError("Missing: $file");
    } else {
        printSuccess("Found: $file");
    }
}

if (empty($missing)) {
    echo "\n";
    printSuccess("All essential files are present!");
} else {
    echo "\n";
    printWarning("Some essential files are missing. Please verify.");
}

echo "\n";
printInfo("Cleanup " . ($dryRun ? "preview " : "") . "completed!");

exit(0);

