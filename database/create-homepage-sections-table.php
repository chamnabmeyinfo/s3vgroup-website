<?php
/**
 * Quick script to create/update homepage_sections table
 * Run this once: php database/create-homepage-sections-table.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;

$db = Connection::getInstance();

try {
    // Check if table exists
    $tableExists = $db->query("SHOW TABLES LIKE 'homepage_sections'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create table with correct schema matching the repository
        $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS homepage_sections (
    id VARCHAR(255) PRIMARY KEY,
    page_id VARCHAR(255) NULL,
    section_type VARCHAR(50) NOT NULL DEFAULT 'custom',
    title VARCHAR(255) NULL,
    content TEXT NULL,
    order_index INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE', 'DRAFT') DEFAULT 'ACTIVE',
    settings TEXT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page_id (page_id),
    INDEX idx_section_type (section_type),
    INDEX idx_status (status),
    INDEX idx_order_index (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
        echo "✓ Created homepage_sections table\n";
    } else {
        // Check if page_id column exists
        $columns = $db->query("SHOW COLUMNS FROM homepage_sections LIKE 'page_id'")->rowCount();
        
        if ($columns === 0) {
            // Add page_id column if it doesn't exist
            try {
                $db->exec(<<<'SQL'
ALTER TABLE homepage_sections 
ADD COLUMN page_id VARCHAR(255) NULL AFTER id,
ADD INDEX idx_page_id (page_id)
SQL
                );
                echo "✓ Added page_id column to homepage_sections table\n";
            } catch (\PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column') === false) {
                    throw $e;
                }
                echo "⚠ page_id column already exists\n";
            }
        }
        
        // Update content and settings columns to TEXT if they're JSON
        $contentType = $db->query("SHOW COLUMNS FROM homepage_sections WHERE Field = 'content'")->fetch(PDO::FETCH_ASSOC);
        if ($contentType && strtoupper($contentType['Type']) === 'JSON') {
            $db->exec("ALTER TABLE homepage_sections MODIFY content TEXT NULL");
            echo "✓ Updated content column to TEXT\n";
        }
        
        $settingsType = $db->query("SHOW COLUMNS FROM homepage_sections WHERE Field = 'settings'")->fetch(PDO::FETCH_ASSOC);
        if ($settingsType && strtoupper($settingsType['Type']) === 'JSON') {
            $db->exec("ALTER TABLE homepage_sections MODIFY settings TEXT NULL");
            echo "✓ Updated settings column to TEXT\n";
        }
        
        // Update status enum to include DRAFT
        $statusType = $db->query("SHOW COLUMNS FROM homepage_sections WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
        if ($statusType && strpos($statusType['Type'], 'DRAFT') === false) {
            $db->exec("ALTER TABLE homepage_sections MODIFY status ENUM('ACTIVE', 'INACTIVE', 'DRAFT') DEFAULT 'ACTIVE'");
            echo "✓ Updated status enum to include DRAFT\n";
        }
        
        echo "✓ homepage_sections table is up to date\n";
    }
    
    echo "\n✅ Success! The homepage_sections table is ready.\n";
    
} catch (\PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

