<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241128_company_showcase') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS ceo_message');
        $pdo->exec('DROP TABLE IF EXISTS company_story_sections');
        $pdo->exec('DROP TABLE IF EXISTS company_story');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS company_story (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    heroImage VARCHAR(500),
    introduction TEXT,
    history TEXT,
    mission TEXT,
    vision TEXT,
    `values` JSON,
    milestones JSON,
    achievements TEXT,
    status ENUM('DRAFT', 'PUBLISHED') DEFAULT 'DRAFT',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS company_story_sections (
    id VARCHAR(255) PRIMARY KEY,
    companyStoryId VARCHAR(255) NOT NULL,
    type ENUM('HISTORY', 'MISSION', 'VISION', 'VALUES', 'MILESTONES', 'ACHIEVEMENTS', 'CUSTOM') DEFAULT 'CUSTOM',
    title VARCHAR(255),
    content TEXT,
    image VARCHAR(500),
    orderIndex INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company_story (companyStoryId),
    INDEX idx_order (orderIndex),
    FOREIGN KEY (companyStoryId) REFERENCES company_story(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS ceo_message (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL DEFAULT 'Message from CEO',
    message TEXT NOT NULL,
    photo VARCHAR(500),
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    signature VARCHAR(500),
    displayOrder INT DEFAULT 0,
    status ENUM('DRAFT', 'PUBLISHED') DEFAULT 'DRAFT',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order (displayOrder)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        ];
    }
};

