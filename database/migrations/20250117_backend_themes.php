<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20250117_backend_themes') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        // Remove backend themes (keep default light/dark)
        $pdo->exec("DELETE FROM themes WHERE slug IN ('macbook', 'windows11', 'dark-pro', 'minimal', 'high-contrast')");
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            // 1. MacBook Style Theme (macOS-inspired)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_macbook', 'MacBook Style', 'macbook', 'Clean macOS-inspired theme with San Francisco font and soft shadows', FALSE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#F5F5F7',
        'surface', '#FFFFFF',
        'primary', '#007AFF',
        'primaryText', '#FFFFFF',
        'text', '#1D1D1F',
        'mutedText', '#86868B',
        'border', '#D2D2D7',
        'error', '#FF3B30',
        'success', '#34C759',
        'warning', '#FF9500'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Arial, sans-serif',
        'headingScale', 1.2,
        'bodySize', 15,
        'lineHeight', 1.47059
    ),
    'radius', JSON_OBJECT(
        'small', 8,
        'medium', 12,
        'large', 20
    ),
    'shadows', JSON_OBJECT(
        'card', '0 2px 8px rgba(0,0,0,0.06)',
        'elevated', '0 4px 16px rgba(0,0,0,0.08)',
        'subtle', '0 1px 3px rgba(0,0,0,0.04)'
    )
))
ON DUPLICATE KEY UPDATE name=name
SQL,
            // 2. Windows 11 Style Theme (Fluent Design)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_windows11', 'Windows 11 Style', 'windows11', 'Modern Windows 11 Fluent Design with Segoe UI and rounded corners', FALSE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#F3F3F3',
        'surface', '#FFFFFF',
        'primary', '#0078D4',
        'primaryText', '#FFFFFF',
        'text', '#202020',
        'mutedText', '#6B6B6B',
        'border', '#E1E1E1',
        'error', '#D13438',
        'success', '#107C10',
        'warning', '#FFB900'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', '"Segoe UI", "Segoe UI Variable", system-ui, -apple-system, sans-serif',
        'headingScale', 1.25,
        'bodySize', 14,
        'lineHeight', 1.5
    ),
    'radius', JSON_OBJECT(
        'small', 4,
        'medium', 8,
        'large', 12
    ),
    'shadows', JSON_OBJECT(
        'card', '0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24)',
        'elevated', '0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23)',
        'subtle', '0 1px 2px rgba(0,0,0,0.08)'
    )
))
ON DUPLICATE KEY UPDATE name=name
SQL,
            // 3. Dark Pro Theme (Professional Dark Mode)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_dark_pro', 'Dark Pro', 'dark-pro', 'Professional dark theme with high contrast and modern aesthetics', FALSE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#0D1117',
        'surface', '#161B22',
        'primary', '#58A6FF',
        'primaryText', '#FFFFFF',
        'text', '#C9D1D9',
        'mutedText', '#8B949E',
        'border', '#30363D',
        'error', '#F85149',
        'success', '#3FB950',
        'warning', '#D29922'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', '-apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif',
        'headingScale', 1.25,
        'bodySize', 14,
        'lineHeight', 1.6
    ),
    'radius', JSON_OBJECT(
        'small', 6,
        'medium', 10,
        'large', 16
    ),
    'shadows', JSON_OBJECT(
        'card', '0 2px 8px rgba(0,0,0,0.4)',
        'elevated', '0 4px 16px rgba(0,0,0,0.5)',
        'subtle', '0 1px 3px rgba(0,0,0,0.3)'
    )
))
ON DUPLICATE KEY UPDATE name=name
SQL,
            // 4. Minimal Theme (Ultra Clean)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_minimal', 'Minimal', 'minimal', 'Ultra-minimal theme with maximum focus and clarity', FALSE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#FAFAFA',
        'surface', '#FFFFFF',
        'primary', '#000000',
        'primaryText', '#FFFFFF',
        'text', '#212121',
        'mutedText', '#757575',
        'border', '#E0E0E0',
        'error', '#D32F2F',
        'success', '#388E3C',
        'warning', '#F57C00'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        'headingScale', 1.15,
        'bodySize', 16,
        'lineHeight', 1.6
    ),
    'radius', JSON_OBJECT(
        'small', 2,
        'medium', 4,
        'large', 8
    ),
    'shadows', JSON_OBJECT(
        'card', '0 1px 3px rgba(0,0,0,0.05)',
        'elevated', '0 2px 6px rgba(0,0,0,0.08)',
        'subtle', '0 1px 2px rgba(0,0,0,0.03)'
    )
))
ON DUPLICATE KEY UPDATE name=name
SQL,
            // 5. High Contrast Theme (Accessibility)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_high_contrast', 'High Contrast', 'high-contrast', 'High contrast theme for better visibility and accessibility', FALSE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#FFFFFF',
        'surface', '#F0F0F0',
        'primary', '#0066CC',
        'primaryText', '#FFFFFF',
        'text', '#000000',
        'mutedText', '#333333',
        'border', '#000000',
        'error', '#CC0000',
        'success', '#006600',
        'warning', '#CC6600'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', 'Arial, "Helvetica Neue", Helvetica, sans-serif',
        'headingScale', 1.3,
        'bodySize', 16,
        'lineHeight', 1.7
    ),
    'radius', JSON_OBJECT(
        'small', 0,
        'medium', 2,
        'large', 4
    ),
    'shadows', JSON_OBJECT(
        'card', '0 2px 4px rgba(0,0,0,0.2)',
        'elevated', '0 4px 8px rgba(0,0,0,0.3)',
        'subtle', '0 1px 2px rgba(0,0,0,0.15)'
    )
))
ON DUPLICATE KEY UPDATE name=name
SQL,
        ];
    }
};

