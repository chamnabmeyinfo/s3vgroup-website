<?php
/**
 * Cleanup Search Logs Database
 * 
 * This script removes the search_logs table from the database
 * as it's no longer needed.
 */

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

echo "🗑️  Cleaning up search_logs database...\n\n";

try {
    $db = Connection::getInstance();
    
    // Check if table exists
    $tableExists = $db->query("SHOW TABLES LIKE 'search_logs'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo "  ℹ️  search_logs table does not exist. Nothing to clean up.\n";
        exit(0);
    }
    
    // Get count before deletion
    $count = $db->query("SELECT COUNT(*) FROM search_logs")->fetchColumn();
    echo "  📊 Found {$count} search log records\n";
    
    // Drop the table
    echo "  🗑️  Dropping search_logs table...\n";
    $db->exec("DROP TABLE IF EXISTS search_logs");
    
    echo "\n✅ Search logs cleanup completed!\n";
    echo "   - Removed {$count} search log records\n";
    echo "   - Dropped search_logs table\n";
    
} catch (\PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

