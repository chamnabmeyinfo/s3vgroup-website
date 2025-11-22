<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241121_initial_schema') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $tables = [
            'portfolio_projects',
            'team_members',
            'quote_requests',
            'product_tags',
            'product_media',
            'products',
            'categories',
        ];

        foreach ($tables as $table) {
            $pdo->exec(sprintf('DROP TABLE IF EXISTS %s', $table));
        }
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS categories (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    priority INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    sku VARCHAR(255),
    summary TEXT,
    description TEXT,
    specs JSON,
    heroImage VARCHAR(500),
    price DECIMAL(12, 2),
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    highlights JSON,
    categoryId VARCHAR(255) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_category (categoryId),
    INDEX idx_status (status),
    FOREIGN KEY (categoryId) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS product_media (
    id VARCHAR(255) PRIMARY KEY,
    url VARCHAR(500) NOT NULL,
    alt VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    productId VARCHAR(255) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (productId),
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS product_tags (
    id VARCHAR(255) PRIMARY KEY,
    label VARCHAR(255) NOT NULL,
    productId VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_product_tag (productId, label),
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS quote_requests (
    id VARCHAR(255) PRIMARY KEY,
    companyName VARCHAR(255) NOT NULL,
    contactName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT,
    items JSON,
    status ENUM('NEW', 'IN_PROGRESS', 'RESOLVED', 'CLOSED') DEFAULT 'NEW',
    source VARCHAR(255),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS team_members (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    bio TEXT,
    photo VARCHAR(500),
    email VARCHAR(255),
    phone VARCHAR(50),
    linkedin VARCHAR(500),
    priority INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS portfolio_projects (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    industry VARCHAR(255) NOT NULL,
    client VARCHAR(255),
    description TEXT,
    challenge TEXT,
    solution TEXT,
    results TEXT,
    heroImage VARCHAR(500),
    images JSON,
    completionDate DATE,
    status ENUM('DRAFT', 'PUBLISHED', 'FEATURED', 'ARCHIVED') DEFAULT 'DRAFT',
    priority INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
INSERT INTO categories (id, name, slug, description, priority) VALUES
('cat_001', 'Forklifts', 'forklifts', 'Electric, diesel, and gas forklifts for material handling', 100),
('cat_002', 'Material Handling', 'material-handling', 'Pallet jacks, hand trucks, and lifting equipment', 90),
('cat_003', 'Storage Solutions', 'storage-solutions', 'Shelving, racks, and warehouse storage systems', 85),
('cat_004', 'Industrial Equipment', 'industrial-equipment', 'Conveyors, dock equipment, and factory machinery', 80),
('cat_005', 'Safety Equipment', 'safety-equipment', 'Safety barriers, signage, and protective equipment', 75),
('cat_006', 'Warehouse Accessories', 'warehouse-accessories', 'Bins, containers, and warehouse organization tools', 70)
ON DUPLICATE KEY UPDATE name=name
SQL,
        ];
    }
};

