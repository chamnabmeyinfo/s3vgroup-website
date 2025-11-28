<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Migration: Create Plugin System Tables
 */
class Migration_20250115_CreatePluginSystem extends Migration
{
    public function up(PDO $pdo): void
    {
        // Create plugins table
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS plugins (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    version VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    path VARCHAR(500) NOT NULL,
    metadata JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $pdo->exec($sql);

        // Create plugin_options table
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS plugin_options (
    id VARCHAR(255) PRIMARY KEY,
    plugin_slug VARCHAR(255) NOT NULL,
    option_key VARCHAR(255) NOT NULL,
    option_value TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_option (plugin_slug, option_key),
    INDEX idx_plugin (plugin_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS plugin_options");
        $pdo->exec("DROP TABLE IF EXISTS plugins");
    }
}

