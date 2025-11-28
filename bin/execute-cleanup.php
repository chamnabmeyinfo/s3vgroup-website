<?php
/**
 * Codebase Cleanup Execution Script
 * Run: php bin/execute-cleanup.php
 * 
 * This script will:
 * 1. Create docs/ directory structure
 * 2. Move documentation files
 * 3. Delete temporary/unused files
 * 4. Generate cleanup report
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = __DIR__ . '/..';
$removed = [];
$moved = [];
$errors = [];

echo "🧹 Codebase Cleanup Execution\n";
echo str_repeat("=", 60) . "\n\n";

// ============================================
// STEP 1: Create docs/ directory structure
// ============================================
echo "📁 STEP 1: Creating docs/ directory structure...\n";

$docDirs = [
    'docs',
    'docs/archive',
    'docs/setup',
];

foreach ($docDirs as $dir) {
    $path = $projectRoot . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "  ✓ Created: $dir/\n";
    } else {
        echo "  ✓ Exists: $dir/\n";
    }
}

echo "\n";

// ============================================
// STEP 2: Move documentation files
// ============================================
echo "📚 STEP 2: Moving documentation files...\n";

$docsToArchive = [
    'ANALYTICS-REMOVAL.md',
    'ANALYTICS-REMOVAL-SUMMARY.md',
    'CLEANUP-SUMMARY.md',
    'PROJECT-CLEANUP-REPORT.md',
    'SEARCH-LOGS-REMOVAL-SUMMARY.md',
    'DRAFT-PRODUCTS-EXTERNAL-IMAGES.md',
    'PUBLISHED-PRODUCTS-ONLY.md',
    'DUPLICATE-DETECTION-IMPROVEMENTS.md',
    'IMAGE-IMPORT-PROGRESS-UPDATES.md',
    'DEMO-DATA-COMPLETE.md',
];

$docsToSetup = [
    'DATABASE-SYNC-GUIDE.md',
    'DATABASE-AUTO-SYNC-GUIDE.md',
    'SCHEMA-SYNC-GUIDE.md',
    'REMOTE-DATABASE-SETUP.md',
    'REMOTE-DATABASE-TROUBLESHOOTING.md',
    'CPANEL-DATABASE-CREDENTIALS-GUIDE.md',
    'QUICK-REMOTE-SETUP.md',
    'LOCAL-VS-SERVER-DIFFERENCES.md',
    'S3VTGROUP-CONNECTION-INFO.md',
    'WORDPRESS-CONNECTION-SETUP.md',
    'WORDPRESS-REMOTE-DB-TROUBLESHOOTING.md',
    'WORDPRESS-SQL-IMPORT-GUIDE.md',
    'WORDPRESS-CONFIG-HELPER.md',
    'WORDPRESS-IMAGE-DOWNLOAD-FIX.md',
    'WORDPRESS-IMPORT-FIXES.md',
    'WOOCOMMERCE-IMPORT-GUIDE.md',
    'WOOCOMMERCE-IMPORT-SETUP.md',
    'GIT-DEPLOYMENT-FIX.md',
    'UPLOAD-CHECKLIST.md',
    'UPLOAD-FIX-404.md',
    'UPLOAD-TEST-CONNECTION.md',
    'TEST-CONNECTION-404-FIX.md',
    'AUTOMATIC-IMAGE-OPTIMIZATION.md',
    'IMAGE-OPTIMIZATION-FEATURES.md',
    'INNOVATION-FEATURES.md',
];

$adminDocsToMove = [
    'admin/ADMIN-RESTRUCTURE.md' => 'docs/',
];

// Move to archive
foreach ($docsToArchive as $file) {
    $from = $projectRoot . '/' . $file;
    $to = $projectRoot . '/docs/archive/' . $file;
    if (file_exists($from)) {
        if (rename($from, $to)) {
            $moved[] = "$file → docs/archive/";
            echo "  ✓ Moved: $file → docs/archive/\n";
        } else {
            $errors[] = "Failed to move: $file";
            echo "  ✗ Failed to move: $file\n";
        }
    }
}

// Move to setup
foreach ($docsToSetup as $file) {
    $from = $projectRoot . '/' . $file;
    $to = $projectRoot . '/docs/setup/' . $file;
    if (file_exists($from)) {
        if (rename($from, $to)) {
            $moved[] = "$file → docs/setup/";
            echo "  ✓ Moved: $file → docs/setup/\n";
        } else {
            $errors[] = "Failed to move: $file";
            echo "  ✗ Failed to move: $file\n";
        }
    }
}

// Move admin docs
foreach ($adminDocsToMove as $from => $toDir) {
    $fromPath = $projectRoot . '/' . $from;
    $toPath = $projectRoot . '/' . $toDir . basename($from);
    if (file_exists($fromPath)) {
        if (rename($fromPath, $toPath)) {
            $moved[] = "$from → $toDir";
            echo "  ✓ Moved: $from → $toDir\n";
        } else {
            $errors[] = "Failed to move: $from";
            echo "  ✗ Failed to move: $from\n";
        }
    }
}

echo "\n";

// ============================================
// STEP 3: Delete temporary/unused files
// ============================================
echo "🗑️  STEP 3: Deleting temporary/unused files...\n";

$filesToDelete = [
    // Translation remnants
    'api/translations/set-language.php',
    
    // Temporary SQL backups
    'tmp/backup-before-sync-2025-11-26-164143.sql',
    'tmp/backup-before-sync-2025-11-26-165704.sql',
    'tmp/backup-before-sync-2025-11-26-170111.sql',
    'tmp/backup-before-sync-2025-11-26-171702.sql',
    'tmp/backup-before-sync-2025-11-26-171902.sql',
    'tmp/backup-before-sync-2025-11-26-173304.sql',
    'tmp/backup-before-sync-2025-11-26-202018.sql',
    'tmp/backup-before-sync-2025-11-26-203235.sql',
    'tmp/backup-before-sync-2025-11-27-073121.sql',
    
    // Temporary/test scripts
    'admin/migrate-to-folders.php',
    'admin/test-db-connection.php',
    'admin/check-api-files.php',
    'database/test-wp-connection.php',
    'database/test-wp-remote-connection.php',
    'database/diagnose-wordpress-db.php',
    'database/setup-wordpress-config.php',
    
    // Old cleanup scripts
    'bin/cleanup.php',
    'bin/comprehensive-cleanup.php',
    'bin/project-cleanup.php',
    'bin/remove-all-product-images.php',
    'bin/assign-optimized-product-images.php',
    'bin/migrate-wordpress-content.php',
    
    // Database cleanup scripts
    'database/cleanup-analytics-data.php',
    'database/cleanup-products.php',
    'database/cleanup-search-logs.php',
    'database/final-cleanup.php',
    'database/demo-data-entry.php',
    
    // Shell scripts
    'FIX-HTACCESS-CONFLICT.sh',
    'bin/auto-sync-scheduled.ps1',
    'bin/auto-sync-schema-scheduled.ps1',
];

foreach ($filesToDelete as $file) {
    $path = $projectRoot . '/' . $file;
    if (file_exists($path)) {
        if (unlink($path)) {
            $removed[] = $file;
            echo "  ✓ Deleted: $file\n";
        } else {
            $errors[] = "Failed to delete: $file";
            echo "  ✗ Failed to delete: $file\n";
        }
    } else {
        echo "  ⊘ Not found: $file (skipped)\n";
    }
}

// Try to remove empty directories
$emptyDirs = [
    'api/translations',
    'api/analytics',
];

foreach ($emptyDirs as $dir) {
    $path = $projectRoot . '/' . $dir;
    if (is_dir($path)) {
        $files = scandir($path);
        if (count($files) <= 2) { // Only . and ..
            if (rmdir($path)) {
                echo "  ✓ Removed empty directory: $dir/\n";
            }
        }
    }
}

echo "\n";

// ============================================
// SUMMARY
// ============================================
echo str_repeat("=", 60) . "\n";
echo "📊 CLEANUP SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "✅ Files moved: " . count($moved) . "\n";
echo "🗑️  Files deleted: " . count($removed) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "⚠️  ERRORS:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

echo "✨ Cleanup completed!\n";
echo "\n";
echo "📝 Next steps:\n";
echo "  1. Review moved documentation in docs/ directory\n";
echo "  2. Test the application to ensure everything works\n";
echo "  3. Commit changes to version control\n";
echo "  4. Update README.md if needed\n";

