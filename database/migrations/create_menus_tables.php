<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('create_menus_tables') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS menu_items');
        $pdo->exec('DROP TABLE IF EXISTS menus');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS menus (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(100) DEFAULT 'primary',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_location (location),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS menu_items (
    id VARCHAR(50) PRIMARY KEY,
    menu_id VARCHAR(50) NOT NULL,
    parent_id VARCHAR(50) DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    type VARCHAR(50) DEFAULT 'custom',
    object_id VARCHAR(255) DEFAULT NULL,
    object_type VARCHAR(50) DEFAULT NULL,
    menu_order INT DEFAULT 0,
    css_classes VARCHAR(500) DEFAULT NULL,
    description TEXT,
    icon VARCHAR(100) DEFAULT NULL,
    target VARCHAR(20) DEFAULT '_self',
    is_mega_menu TINYINT(1) DEFAULT 0,
    mega_menu_columns INT DEFAULT 3,
    mega_menu_image VARCHAR(500) DEFAULT NULL,
    mega_menu_content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_menu_id (menu_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_menu_order (menu_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
INSERT INTO menus (id, name, slug, location, description)
VALUES ('main-menu-default', 'Main Menu', 'main-menu', 'primary', 'Primary navigation menu')
ON DUPLICATE KEY UPDATE name=name
SQL,
        ];
    }
};
