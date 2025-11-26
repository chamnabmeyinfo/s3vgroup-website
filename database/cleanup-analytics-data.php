<?php
/**
 * Cleanup Analytics Data
 * 
 * Removes all records from the analytics_events table.
 * The table structure is preserved for potential future use.
 * 
 * Run: php database/cleanup-analytics-data.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

echo "🧹 Cleaning up analytics data...\n\n";

try {
    $db = Connection::getInstance();
    
    // Check if table exists
    $tableExists = $db->query("SHOW TABLES LIKE 'analytics_events'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo "  ℹ️  Table 'analytics_events' does not exist. Nothing to clean.\n";
        exit(0);
    }
    
    // Get count before deletion
    $countBefore = $db->query("SELECT COUNT(*) FROM analytics_events")->fetchColumn();
    
    if ($countBefore == 0) {
        echo "  ℹ️  No analytics records found. Database is already clean.\n";
        exit(0);
    }
    
    echo "  📊 Found $countBefore analytics records\n";
    echo "  🗑️  Deleting all analytics records...\n";
    
    // Delete all records
    $db->exec("DELETE FROM analytics_events");
    
    // Verify deletion
    $countAfter = $db->query("SELECT COUNT(*) FROM analytics_events")->fetchColumn();
    
    if ($countAfter == 0) {
        echo "  ✅ Successfully deleted $countBefore analytics records\n";
        echo "  ℹ️  Table structure preserved for potential future use\n";
    } else {
        echo "  ⚠️  Warning: Some records may still exist ($countAfter remaining)\n";
    }
    
    echo "\n✨ Analytics data cleanup complete!\n";
    
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

