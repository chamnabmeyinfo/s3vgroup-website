<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20250116_themes_system') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS user_theme_preferences');
        $pdo->exec('DROP TABLE IF EXISTS themes');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS themes (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    config JSON NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_is_default (is_default),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS user_theme_preferences (
    id VARCHAR(255) PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    theme_id VARCHAR(255) NOT NULL,
    scope VARCHAR(50) DEFAULT 'public_frontend',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_theme (theme_id),
    INDEX idx_scope (scope),
    UNIQUE KEY unique_user_scope (user_id, scope),
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            // Seed default Light theme (Apple-like neutral palette)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_light', 'Light', 'light', 'Clean, minimal light theme with neutral colors and soft shadows', TRUE, TRUE, 
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#FFFFFF',
        'surface', '#F5F5F7',
        'primary', '#007AFF',
        'primaryText', '#FFFFFF',
        'text', '#111111',
        'mutedText', '#8E8E93',
        'border', '#D1D1D6',
        'error', '#FF3B30',
        'success', '#34C759',
        'warning', '#FF9500'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        'headingScale', 1.25,
        'bodySize', 16,
        'lineHeight', 1.5
    ),
    'radius', JSON_OBJECT(
        'small', 6,
        'medium', 12,
        'large', 20
    ),
    'shadows', JSON_OBJECT(
        'card', '0 2px 8px rgba(0,0,0,0.08)',
        'elevated', '0 4px 16px rgba(0,0,0,0.12)',
        'subtle', '0 1px 3px rgba(0,0,0,0.05)'
    )
))
ON DUPLICATE KEY UPDATE name=name
SQL,
            // Seed default Dark theme (Apple-like dark mode)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_dark', 'Dark', 'dark', 'Elegant dark theme with deep colors and refined contrast', FALSE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#000000',
        'surface', '#1C1C1E',
        'primary', '#0A84FF',
        'primaryText', '#FFFFFF',
        'text', '#FFFFFF',
        'mutedText', '#8E8E93',
        'border', '#38383A',
        'error', '#FF453A',
        'success', '#32D74B',
        'warning', '#FF9F0A'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        'headingScale', 1.25,
        'bodySize', 16,
        'lineHeight', 1.5
    ),
    'radius', JSON_OBJECT(
        'small', 6,
        'medium', 12,
        'large', 20
    ),
    'shadows', JSON_OBJECT(
        'card', '0 2px 8px rgba(0,0,0,0.3)',
        'elevated', '0 4px 16px rgba(0,0,0,0.4)',
        'subtle', '0 1px 3px rgba(0,0,0,0.2)'
    )
))
ON DUPLICATE KEY UPDATE name=name
SQL,
        ];
    }
};

