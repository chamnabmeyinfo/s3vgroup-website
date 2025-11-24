<?php
/**
 * Comprehensive Code Cleanup
 * 
 * Removes unnecessary files, scripts, and documentation
 */

declare(strict_types=1);

echo "🧹 Starting comprehensive code cleanup...\n\n";

$filesToRemove = [];
$directoriesToCheck = [];

// ============================================
// 1. TEMPORARY/DIAGNOSTIC SCRIPTS (One-time use)
// ============================================
echo "📁 Identifying temporary diagnostic scripts...\n";
$tempScripts = [
    // Image checking/diagnostic scripts (keep only essential ones)
    'bin/browser-image-check.php',
    'bin/check-image-sizes.php',
    'bin/check-live-image-files.php',
    'bin/check-live-images.php',
    'bin/check-live-website-images.php',
    'bin/diagnose-product-images.php',
    'bin/find-missing-images.php',
    'bin/list-missing-images.php',
    'bin/test-image-access.php',
    'bin/verify-image-accessibility.php',
    'bin/verify-live-images.php',
    
    // One-time fix scripts
    'bin/fix-all-image-urls.php',
    'bin/fix-image-urls.php',
    'bin/fix-localhost-urls-in-db.php',
    'bin/fix-localhost-urls.php',
    'bin/fix-team-members-schema.php',
    'bin/remove-large-from-git.php',
    'bin/remove-large-images.php',
    'bin/upload-images-to-live.php',
    'bin/sync-images-to-live.php',
    
    // Temporary optimization scripts (keep only the main one)
    'bin/force-compress-images.php',
    'bin/optimize-all-images.php',
    'bin/check-image-sizes.php',
    
    // Old migration/seed scripts (keep only essential)
    'bin/migrate.php', // Keep migrate-wordpress-content.php if needed
    'bin/seed.php', // Keep specific seed scripts
    'bin/seed-sample-data.php',
    'bin/seed-team-members.php',
    'bin/seed-warehouse-products.php',
    'bin/reset-sliders.php',
    
    // Test scripts
    'bin/test-live-connection.php',
    
    // Upload helper scripts (one-time use)
    'bin/upload-missing-images.bat',
    'bin/upload-missing-images.sh',
    'bin/generate-upload-list.php',
    
    // Old sync scripts (keep only the main ones)
    'bin/sync-database-to-live-incremental.php',
    'bin/sync-database-to-live.php',
    'bin/sync-schema-to-live.php',
];

foreach ($tempScripts as $script) {
    $fullPath = __DIR__ . '/../' . $script;
    if (file_exists($fullPath)) {
        $filesToRemove[] = $script;
    }
}

// ============================================
// 2. REDUNDANT DOCUMENTATION FILES
// ============================================
echo "📄 Identifying redundant documentation...\n";
$redundantDocs = [
    // Temporary fix guides (issues already resolved)
    'FIX-MISSING-IMAGES.md',
    'PRODUCT-IMAGES-NOT-SHOWING-FIX.md',
    'IMAGE-LOADING-ERROR-REPORT.md',
    'IMAGE-CLEANUP-COMPLETE.md',
    'CSS-COMPRESSION-DISABLED.md',
    'ALL-IMAGES-REPORT.md',
    
    // Setup guides (keep only main ones)
    'AUTO-IMPORT-DATABASE.md', // Redundant with DATABASE-SYNC-GUIDE.md
    'AUTO-SYNC-SETUP.md', // Redundant with DATABASE-SYNC-GUIDE.md
    'AUTO-SCHEMA-SYNC-SETUP.md', // Redundant with SCHEMA-SYNC-GUIDE.md
    'LIVE-SETUP-GUIDE.md', // Info in README.md
    'LOCAL-SETUP.md', // Info in README.md
    
    // Redundant guides
    'OPTIMIZE-IMAGES-GUIDE.md', // Info in README.md
    'ENABLE-GD-EXTENSION.md', // Simple instructions, can be in README
];

foreach ($redundantDocs as $doc) {
    $fullPath = __DIR__ . '/../' . $doc;
    if (file_exists($fullPath)) {
        $filesToRemove[] = $doc;
    }
}

// ============================================
// 3. OLD/UNUSED CONFIG FILES
// ============================================
echo "⚙️  Checking config files...\n";
$oldConfigs = [
    'config/database.local.php', // Should use .env instead
];

foreach ($oldConfigs as $config) {
    $fullPath = __DIR__ . '/../' . $config;
    if (file_exists($fullPath)) {
        // Check if it's actually used
        $content = file_get_contents($fullPath);
        if (strpos($content, 'localhost') !== false || strpos($content, '127.0.0.1') !== false) {
            // Might still be in use, skip for now
        } else {
            $filesToRemove[] = $config;
        }
    }
}

// ============================================
// 4. DISPLAY SUMMARY
// ============================================
echo "\n═══════════════════════════════════════\n";
echo "  CLEANUP SUMMARY\n";
echo "═══════════════════════════════════════\n\n";

echo "📊 Files to remove: " . count($filesToRemove) . "\n\n";

if (count($filesToRemove) > 0) {
    echo "Files identified for removal:\n";
    foreach ($filesToRemove as $file) {
        $size = file_exists(__DIR__ . '/../' . $file) ? filesize(__DIR__ . '/../' . $file) : 0;
        $sizeKB = round($size / 1024, 2);
        echo "  - {$file} ({$sizeKB}KB)\n";
    }
    
    echo "\n⚠️  This will permanently delete these files!\n";
    echo "Press Ctrl+C to cancel, or Enter to continue...\n";
    // readline(); // Uncomment for interactive mode
    
    $removed = 0;
    $failed = 0;
    $totalSize = 0;
    
    foreach ($filesToRemove as $file) {
        $fullPath = __DIR__ . '/../' . $file;
        if (file_exists($fullPath)) {
            try {
                $size = filesize($fullPath);
                unlink($fullPath);
                echo "✅ Deleted: {$file}\n";
                $removed++;
                $totalSize += $size;
            } catch (Exception $e) {
                echo "❌ Failed: {$file} - " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    }
    
    echo "\n═══════════════════════════════════════\n";
    echo "  CLEANUP RESULTS\n";
    echo "═══════════════════════════════════════\n\n";
    echo "✅ Removed: {$removed} files\n";
    echo "❌ Failed: {$failed} files\n";
    echo "💾 Space freed: " . round($totalSize / 1024 / 1024, 2) . "MB\n";
} else {
    echo "✅ No unnecessary files found!\n";
}

// ============================================
// 5. KEEP ESSENTIAL FILES
// ============================================
echo "\n✅ Essential files kept:\n";
$essentialFiles = [
    'bin/check-all-website-images.php', // Comprehensive image checker
    'bin/verify-all-product-images.php', // Product image verifier
    'bin/optimize-all-to-1mb.php', // Image optimizer
    'bin/db-manager.php', // Database manager
    'bin/auto-sync-database.php', // Auto sync
    'bin/auto-sync-schema.php', // Schema sync
    'bin/sync-database.php', // Manual sync
    'bin/assign-verified-images.php', // Image assignment
    'bin/migrate-wordpress-content.php', // WordPress migration
    'bin/extract-logo-colors.php', // Logo color extractor
    'bin/project-cleanup.php', // Project cleanup
    'bin/cleanup.php', // General cleanup
    'README.md', // Main documentation
    'PERFORMANCE-RECOMMENDATIONS.md', // Performance guide
    'DATABASE-SYNC-GUIDE.md', // Database sync guide
    'SCHEMA-SYNC-GUIDE.md', // Schema sync guide
    'DATABASE-MANAGER-GUIDE.md', // DB manager guide
    'FEATURES-OVERVIEW.md', // Features overview
    'ADMIN-ORGANIZATION.md', // Admin guide
];

foreach ($essentialFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        echo "  ✅ {$file}\n";
    }
}

echo "\n✨ Cleanup completed!\n";

