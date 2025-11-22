<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241201_pages_system') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            try {
                $pdo->exec($statement);
            } catch (\PDOException $e) {
                // Ignore "duplicate column" errors if migration is run multiple times
                if (strpos($e->getMessage(), 'Duplicate column name') === false && 
                    strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS pages');
        // Remove page_id column from homepage_sections
        try {
            $pdo->exec('ALTER TABLE homepage_sections DROP COLUMN IF EXISTS page_id');
        } catch (\PDOException $e) {
            // Ignore if column doesn't exist
        }
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS pages (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    page_type ENUM('page', 'post', 'custom', 'template') DEFAULT 'page',
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    template VARCHAR(100),
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    featured_image VARCHAR(500),
    settings JSON,
    priority INT DEFAULT 0,
    parent_id VARCHAR(255) NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_type (page_type),
    INDEX idx_parent (parent_id),
    FOREIGN KEY (parent_id) REFERENCES pages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
ALTER TABLE homepage_sections 
ADD COLUMN page_id VARCHAR(255) NULL AFTER id,
ADD INDEX idx_page (page_id),
ADD FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
SQL,
            <<<'SQL'
INSERT INTO pages (id, title, slug, description, page_type, status, meta_title, meta_description, priority)
VALUES 
('page_home', 'Homepage', 'home', 'Main homepage of the website', 'page', 'PUBLISHED', 'Home', 'Welcome to our website', 100),
('page_about', 'About Us', 'about', 'About our company', 'page', 'DRAFT', 'About Us', 'Learn more about our company', 90),
('page_contact', 'Contact Us', 'contact', 'Contact information', 'page', 'DRAFT', 'Contact Us', 'Get in touch with us', 80),
('page_services', 'Services', 'services', 'Our services', 'page', 'DRAFT', 'Services', 'Our services and solutions', 70)
ON DUPLICATE KEY UPDATE title = title
SQL,
        ];
    }
};

