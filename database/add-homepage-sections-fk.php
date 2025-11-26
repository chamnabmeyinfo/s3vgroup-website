<?php
/**
 * Add foreign key constraint to homepage_sections if pages table exists
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

$db = Connection::getInstance();

try {
    // Check if pages table exists
    $pagesExists = $db->query("SHOW TABLES LIKE 'pages'")->rowCount() > 0;
    
    if ($pagesExists) {
        // Check if foreign key already exists
        $fkExists = false;
        try {
            $result = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'homepage_sections' AND COLUMN_NAME = 'page_id' AND REFERENCED_TABLE_NAME = 'pages'")->fetch(PDO::FETCH_ASSOC);
            $fkExists = $result !== false;
        } catch (\PDOException $e) {
            // Ignore
        }
        
        if (!$fkExists) {
            $db->exec("ALTER TABLE homepage_sections ADD CONSTRAINT fk_homepage_sections_page_id FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE");
            echo "✓ Added foreign key constraint\n";
        } else {
            echo "✓ Foreign key constraint already exists\n";
        }
    } else {
        echo "⚠ Pages table does not exist, skipping foreign key\n";
    }
    
    echo "\n✅ Done!\n";
    
} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "✓ Foreign key already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

