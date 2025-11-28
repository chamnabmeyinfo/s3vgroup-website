<?php
/**
 * Documentation Cleanup Script
 * Removes unnecessary .md files and organizes the rest
 * Run: php bin/cleanup-documentation.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = __DIR__ . '/..';
$deleted = [];
$moved = [];
$errors = [];

echo "📚 Documentation Cleanup\n";
echo str_repeat("=", 60) . "\n\n";

// ============================================
// STEP 1: Create docs/ directory structure
// ============================================
echo "📁 STEP 1: Creating docs/ directory structure...\n";

$docDirs = [
    'docs',
    'docs/guides',
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
// STEP 2: Move files to docs/
// ============================================
echo "📦 STEP 2: Moving files to docs/...\n";

$filesToMove = [
    // Guides
    'ADMIN-ORGANIZATION.md' => 'docs/guides/',
    'DATABASE-MANAGER-GUIDE.md' => 'docs/guides/',
    'IMAGE-OPTIMIZATION-GUIDE.md' => 'docs/guides/',
    'PERFORMANCE-RECOMMENDATIONS.md' => 'docs/guides/',
    
    // Setup
    'DATABASE-SYNC-GUIDE.md' => 'docs/setup/',
    'SCHEMA-SYNC-GUIDE.md' => 'docs/setup/',
    'REMOTE-DATABASE-SETUP.md' => 'docs/setup/',
    'QUICK-REMOTE-SETUP.md' => 'docs/setup/',
];

foreach ($filesToMove as $file => $targetDir) {
    $from = $projectRoot . '/' . $file;
    $to = $projectRoot . '/' . $targetDir . $file;
    if (file_exists($from)) {
        if (rename($from, $to)) {
            $moved[] = "$file → $targetDir";
            echo "  ✓ Moved: $file → $targetDir\n";
        } else {
            $errors[] = "Failed to move: $file";
            echo "  ✗ Failed to move: $file\n";
        }
    } else {
        echo "  ⊘ Not found: $file (skipped)\n";
    }
}

echo "\n";

// ============================================
// STEP 3: Delete unnecessary files
// ============================================
echo "🗑️  STEP 3: Deleting unnecessary documentation...\n";

$filesToDelete = [
    // Historical fixes
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
    
    // WordPress/WooCommerce (one-time setup)
    'WORDPRESS-CONNECTION-SETUP.md',
    'WORDPRESS-REMOTE-DB-TROUBLESHOOTING.md',
    'WORDPRESS-SQL-IMPORT-GUIDE.md',
    'WORDPRESS-CONFIG-HELPER.md',
    'WORDPRESS-IMAGE-DOWNLOAD-FIX.md',
    'WORDPRESS-IMPORT-FIXES.md',
    'WOOCOMMERCE-IMPORT-GUIDE.md',
    'WOOCOMMERCE-IMPORT-SETUP.md',
    
    // Troubleshooting (already resolved)
    'REMOTE-DATABASE-TROUBLESHOOTING.md',
    'CPANEL-DATABASE-CREDENTIALS-GUIDE.md',
    'LOCAL-VS-SERVER-DIFFERENCES.md',
    'S3VTGROUP-CONNECTION-INFO.md',
    'TEST-CONNECTION-404-FIX.md',
    'UPLOAD-FIX-404.md',
    'UPLOAD-TEST-CONNECTION.md',
    'GIT-DEPLOYMENT-FIX.md',
    'UPLOAD-CHECKLIST.md',
    
    // Redundant feature docs
    'AUTOMATIC-IMAGE-OPTIMIZATION.md',
    'IMAGE-OPTIMIZATION-FEATURES.md',
    'INNOVATION-FEATURES.md',
    'DATABASE-AUTO-SYNC-GUIDE.md',
    
    // Admin docs
    'admin/ADMIN-RESTRUCTURE.md',
];

foreach ($filesToDelete as $file) {
    $path = $projectRoot . '/' . $file;
    if (file_exists($path)) {
        if (unlink($path)) {
            $deleted[] = $file;
            echo "  ✓ Deleted: $file\n";
        } else {
            $errors[] = "Failed to delete: $file";
            echo "  ✗ Failed to delete: $file\n";
        }
    } else {
        echo "  ⊘ Not found: $file (skipped)\n";
    }
}

echo "\n";

// ============================================
// STEP 4: List remaining files
// ============================================
echo "📋 STEP 4: Remaining documentation files...\n";

$keepFiles = [
    'README.md',
    'FEATURES-OVERVIEW.md',
    'PROJECT-RESTRUCTURE-PLAN.md',
    'SYSTEM-STATUS.md',
    'VERIFICATION-REPORT.md',
];

foreach ($keepFiles as $file) {
    $path = $projectRoot . '/' . $file;
    if (file_exists($path)) {
        echo "  ✅ $file (kept in root)\n";
    } else {
        echo "  ⚠️  $file (not found!)\n";
    }
}

echo "\n";

// ============================================
// SUMMARY
// ============================================
echo str_repeat("=", 60) . "\n";
echo "📊 CLEANUP SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "📦 Files moved: " . count($moved) . "\n";
echo "🗑️  Files deleted: " . count($deleted) . "\n";
echo "✅ Files kept in root: " . count($keepFiles) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (count($moved) > 0) {
    echo "📦 MOVED FILES:\n";
    foreach ($moved as $item) {
        echo "  - $item\n";
    }
    echo "\n";
}

if (count($deleted) > 0) {
    echo "🗑️  DELETED FILES:\n";
    foreach ($deleted as $item) {
        echo "  - $item\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERRORS:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

echo "✨ Documentation cleanup completed!\n";
echo "\n";
echo "📊 Before: ~46 .md files\n";
echo "📊 After: 13 .md files (5 in root + 8 in docs/)\n";
echo "\n";
echo "📝 Next steps:\n";
echo "  1. Review the remaining documentation\n";
echo "  2. Update README.md if needed\n";
echo "  3. Test the application\n";
echo "  4. Commit changes\n";

