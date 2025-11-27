<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241203_translations') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS translations');
        $pdo->exec('DROP TABLE IF EXISTS languages');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS languages (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    native_name VARCHAR(100) NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL,
    flag VARCHAR(10),
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS translations (
    id VARCHAR(255) PRIMARY KEY,
    language_code VARCHAR(10) NOT NULL,
    key_name VARCHAR(255) NOT NULL,
    namespace VARCHAR(100) DEFAULT 'general',
    value TEXT,
    is_auto_translated BOOLEAN DEFAULT FALSE,
    needs_review BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_translation (language_code, key_name, namespace),
    INDEX idx_language (language_code),
    INDEX idx_key (key_name),
    INDEX idx_namespace (namespace),
    INDEX idx_needs_review (needs_review),
    FOREIGN KEY (language_code) REFERENCES languages(code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
INSERT INTO languages (id, name, native_name, code, flag, is_default, is_active, sort_order) VALUES
('en', 'English', 'English', 'en', '🇺🇸', TRUE, TRUE, 1),
('km', 'Khmer', 'ភាសាខ្មែរ', 'km', '🇰🇭', FALSE, TRUE, 2),
('zh', 'Chinese', '中文', 'zh', '🇨🇳', FALSE, TRUE, 3),
('th', 'Thai', 'ไทย', 'th', '🇹🇭', FALSE, TRUE, 4)
SQL,
        ];
    }
};

