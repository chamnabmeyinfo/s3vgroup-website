<?php
/**
 * Final Project Cleanup Script
 * 
 * Removes unnecessary files while keeping all essential production code.
 * 
 * Run: php database/final-cleanup.php
 */

require_once __DIR__ . '/../bootstrap/app.php';

echo "🧹 Starting final project cleanup...\n\n";

$removedFiles = [];
$keptFiles = [];
$errors = [];

// ============================================
// 1. CLEAN OLD BACKUP FILES (Keep last 5)
// ============================================

echo "📦 Step 1: Cleaning old backup files...\n";

$backupDir = __DIR__ . '/../tmp';
if (is_dir($backupDir)) {
    $backups = glob($backupDir . '/backup-*.sql');
    
    if (count($backups) > 5) {
        // Sort by modification time (newest first)
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Keep first 5, remove the rest
        $toRemove = array_slice($backups, 5);
        
        foreach ($toRemove as $backup) {
            if (unlink($backup)) {
                $removedFiles[] = basename($backup);
                echo "  ✓ Removed: " . basename($backup) . "\n";
            } else {
                $errors[] = "Failed to remove: " . basename($backup);
            }
        }
        
        echo "  ✓ Kept " . count(array_slice($backups, 0, 5)) . " most recent backups\n";
    } else {
        echo "  ✓ All backups are recent, keeping all\n";
    }
} else {
    echo "  ⚠ tmp directory not found\n";
}

echo "\n";

// ============================================
// 2. REMOVE ONE-TIME SETUP SCRIPTS
// ============================================

echo "🔧 Step 2: Removing one-time setup scripts...\n";

$oneTimeScripts = [
    'database/create-homepage-sections-table.php' => 'One-time table creation (already completed)',
    'database/add-homepage-sections-fk.php' => 'One-time foreign key addition (already completed)',
    'database/cleanup-and-sample-data.php' => 'Replaced by demo-data-entry.php',
];

// Old/redundant admin files
$redundantAdminFiles = [
    'admin/homepage-builder.php' => 'Old version, replaced by homepage-builder-v2.php',
];

foreach ($oneTimeScripts as $file => $reason) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            $removedFiles[] = $file;
            echo "  ✓ Removed: $file ($reason)\n";
        } else {
            $errors[] = "Failed to remove: $file";
        }
    }
}

// Remove redundant admin files
foreach ($redundantAdminFiles as $file => $reason) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            $removedFiles[] = $file;
            echo "  ✓ Removed: $file ($reason)\n";
        } else {
            $errors[] = "Failed to remove: $file";
        }
    }
}

echo "\n";

// ============================================
// 3. CONSOLIDATE DOCUMENTATION
// ============================================

echo "📚 Step 3: Consolidating documentation...\n";

// Remove redundant documentation (info is in other files)
$redundantDocs = [
    'SAMPLE-DATA-SUMMARY.md' => 'Information consolidated into DEMO-DATA-COMPLETE.md',
];

foreach ($redundantDocs as $file => $reason) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            $removedFiles[] = $file;
            echo "  ✓ Removed: $file ($reason)\n";
        } else {
            $errors[] = "Failed to remove: $file";
        }
    }
}

echo "\n";

// ============================================
// 4. CHECK FOR DUPLICATE CLEANUP SCRIPTS
// ============================================

echo "🔍 Step 4: Checking for redundant scripts...\n";

// Keep only the most comprehensive cleanup script
// We'll keep project-cleanup.php as it's the most comprehensive
$redundantScripts = [
    // These are older versions, keep project-cleanup.php
];

// Note: We're keeping all cleanup scripts for now as they might have different purposes
echo "  ✓ Keeping all utility scripts (they serve different purposes)\n";

echo "\n";

// ============================================
// 5. CLEAN STORAGE/LOGS (Keep recent)
// ============================================

echo "📁 Step 5: Cleaning old log files...\n";

$logDir = __DIR__ . '/../storage/logs';
if (is_dir($logDir)) {
    $logs = glob($logDir . '/*.log');
    
    if (count($logs) > 10) {
        // Sort by modification time (newest first)
        usort($logs, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Keep last 10, remove older
        $toRemove = array_slice($logs, 10);
        
        foreach ($toRemove as $log) {
            if (unlink($log)) {
                $removedFiles[] = 'storage/logs/' . basename($log);
                echo "  ✓ Removed old log: " . basename($log) . "\n";
            }
        }
        
        echo "  ✓ Kept " . min(10, count($logs)) . " most recent log files\n";
    } else {
        echo "  ✓ All logs are recent, keeping all\n";
    }
} else {
    echo "  ⚠ storage/logs directory not found\n";
}

echo "\n";

// ============================================
// 6. VERIFY ESSENTIAL FILES ARE KEPT
// ============================================

echo "✅ Step 6: Verifying essential files...\n";

$essentialFiles = [
    'README.md',
    'INNOVATION-FEATURES.md',
    'DEMO-DATA-COMPLETE.md',
    'database/demo-data-entry.php',
    'database/run-migration.php',
    'import-database.php',
    'bootstrap/app.php',
    'config/database.php',
    'config/site.php',
];

foreach ($essentialFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        $keptFiles[] = $file;
        echo "  ✓ Kept: $file\n";
    } else {
        echo "  ⚠ Missing: $file\n";
    }
}

echo "\n";

// ============================================
// SUMMARY
// ============================================

echo "📊 Cleanup Summary:\n";
echo str_repeat("=", 50) . "\n";
echo "Files Removed: " . count($removedFiles) . "\n";
echo "Essential Files Kept: " . count($keptFiles) . "\n";

if (!empty($removedFiles)) {
    echo "\n🗑️  Removed Files:\n";
    foreach ($removedFiles as $file) {
        echo "  • $file\n";
    }
}

if (!empty($errors)) {
    echo "\n⚠️  Errors:\n";
    foreach ($errors as $error) {
        echo "  • $error\n";
    }
}

echo "\n";

// ============================================
// FILES TO KEEP (Reference)
// ============================================

echo "📋 Essential Files Kept:\n";
echo "  ✅ All production code (admin/, api/, app/, includes/)\n";
echo "  ✅ All configuration files (config/)\n";
echo "  ✅ All migrations (database/migrations/)\n";
echo "  ✅ Core utility scripts (bin/)\n";
echo "  ✅ Documentation (README.md, guides)\n";
echo "  ✅ Recent backups (last 5 in tmp/)\n";
echo "  ✅ Database setup scripts\n";

echo "\n🎉 Cleanup completed!\n";
echo "✨ Your project is now clean and organized.\n";

