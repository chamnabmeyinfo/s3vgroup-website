<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20250118_design_all_themes') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        // Reset to original light theme as default
        $pdo->exec("UPDATE themes SET is_default = FALSE WHERE slug != 'light'");
        $pdo->exec("UPDATE themes SET is_default = TRUE WHERE slug = 'light'");
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            // First, unset all defaults
            <<<'SQL'
UPDATE themes SET is_default = FALSE WHERE is_default = TRUE
SQL,
            // 1. Ant Elite Default - New Default Theme (Modern Professional)
            <<<'SQL'
REPLACE INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_ant_elite_default', 'Ant Elite Default', 'ant-elite-default', 'Modern professional theme with balanced colors, clean typography, and refined aesthetics. Optimized for productivity and visual comfort.', TRUE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#FAFBFC',
        'surface', '#FFFFFF',
        'primary', '#2563EB',
        'primaryText', '#FFFFFF',
        'text', '#1F2937',
        'mutedText', '#6B7280',
        'border', '#E5E7EB',
        'error', '#DC2626',
        'success', '#059669',
        'warning', '#D97706',
        'accent', '#7C3AED',
        'secondary', '#10B981',
        'tertiary', '#F59E0B'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        'headingScale', 1.25,
        'bodySize', 15,
        'lineHeight', 1.6,
        'fontWeightNormal', 400,
        'fontWeightMedium', 500,
        'fontWeightSemibold', 600,
        'fontWeightBold', 700,
        'letterSpacing', 'normal'
    ),
    'radius', JSON_OBJECT(
        'small', 6,
        'medium', 10,
        'large', 16,
        'pill', 9999
    ),
    'shadows', JSON_OBJECT(
        'card', '0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06)',
        'elevated', '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)',
        'subtle', '0 1px 2px rgba(0,0,0,0.05)',
        'button', '0 1px 3px rgba(0,0,0,0.1)',
        'buttonHover', '0 2px 6px rgba(0,0,0,0.15)'
    )
))
SQL,
            // 2. MacBook Style - macOS Big Sur/Monterey (2020-2022) inspired
            <<<'SQL'
REPLACE INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_macbook', 'MacBook Style', 'macbook', 'Authentic macOS Big Sur/Monterey design (2020-2022) with SF Pro font, glassmorphism effects, and Apple Human Interface Guidelines. Perfect macOS experience.', FALSE, TRUE,
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
        'warning', '#FF9500',
        'accent', '#5856D6',
        'secondary', '#AF52DE',
        'tertiary', '#FF2D55'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Arial, sans-serif',
        'headingScale', 1.2,
        'bodySize', 15,
        'lineHeight', 1.47059,
        'fontWeightNormal', 400,
        'fontWeightMedium', 500,
        'fontWeightSemibold', 600,
        'fontWeightBold', 700,
        'letterSpacing', '-0.01em'
    ),
    'radius', JSON_OBJECT(
        'small', 8,
        'medium', 14,
        'large', 24,
        'pill', 9999
    ),
    'shadows', JSON_OBJECT(
        'card', '0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04)',
        'elevated', '0 8px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)',
        'subtle', '0 1px 2px rgba(0,0,0,0.04)',
        'button', '0 1px 3px rgba(0,0,0,0.1)',
        'buttonHover', '0 2px 6px rgba(0,0,0,0.15)'
    ),
    'macos', JSON_OBJECT(
        'version', 'Big Sur / Monterey (2020-2022)',
        'designSystem', 'Apple Human Interface Guidelines',
        'fontSystem', 'SF Pro Display & SF Pro Text',
        'effects', 'Glassmorphism, Vibrancy, Depth',
        'buttonStyle', 'Rounded with soft shadows',
        'linkStyle', 'Blue with underline on hover',
        'accentColor', 'System Blue (#007AFF)'
    )
))
SQL,
            // 3. Windows 11 Style - Fluent Design System
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_windows11', 'Windows 11 Style', 'windows11', 'Windows 11 Fluent Design with Segoe UI Variable, Mica effects, and rounded corners', FALSE, TRUE,
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
        'fontFamily', '"Segoe UI Variable", "Segoe UI", system-ui, -apple-system, sans-serif',
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
        'card', '0 2px 4px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06)',
        'elevated', '0 4px 8px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08)',
        'subtle', '0 1px 2px rgba(0,0,0,0.05)'
    )
))
ON DUPLICATE KEY UPDATE 
    name = 'Windows 11 Style',
    description = 'Windows 11 Fluent Design with Segoe UI Variable, Mica effects, and rounded corners',
    config = JSON_OBJECT(
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
            'fontFamily', '"Segoe UI Variable", "Segoe UI", system-ui, -apple-system, sans-serif',
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
            'card', '0 2px 4px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06)',
            'elevated', '0 4px 8px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08)',
            'subtle', '0 1px 2px rgba(0,0,0,0.05)'
        )
    )
SQL,
            // 4. Dark Pro - Professional Dark Theme (GitHub-inspired)
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_dark_pro', 'Dark Pro', 'dark-pro', 'Professional dark theme with high contrast, optimized for extended use', FALSE, TRUE,
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
        'card', '0 3px 6px rgba(0,0,0,0.4), 0 2px 4px rgba(0,0,0,0.3)',
        'elevated', '0 8px 16px rgba(0,0,0,0.5), 0 4px 8px rgba(0,0,0,0.4)',
        'subtle', '0 1px 3px rgba(0,0,0,0.3)'
    )
))
ON DUPLICATE KEY UPDATE 
    name = 'Dark Pro',
    description = 'Professional dark theme with high contrast, optimized for extended use',
    config = JSON_OBJECT(
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
            'card', '0 3px 6px rgba(0,0,0,0.4), 0 2px 4px rgba(0,0,0,0.3)',
            'elevated', '0 8px 16px rgba(0,0,0,0.5), 0 4px 8px rgba(0,0,0,0.4)',
            'subtle', '0 1px 3px rgba(0,0,0,0.3)'
        )
    )
SQL,
            // 5. Minimal - Ultra Clean & Minimalist
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_minimal', 'Minimal', 'minimal', 'Ultra-minimal theme with maximum focus, minimal borders, and clean aesthetics', FALSE, TRUE,
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
ON DUPLICATE KEY UPDATE 
    name = 'Minimal',
    description = 'Ultra-minimal theme with maximum focus, minimal borders, and clean aesthetics',
    config = JSON_OBJECT(
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
    )
SQL,
            // 6. High Contrast - Accessibility Focused
            <<<'SQL'
INSERT INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_high_contrast', 'High Contrast', 'high-contrast', 'High contrast theme for maximum visibility and accessibility compliance', FALSE, TRUE,
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
ON DUPLICATE KEY UPDATE 
    name = 'High Contrast',
    description = 'High contrast theme for maximum visibility and accessibility compliance',
    config = JSON_OBJECT(
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
    )
SQL,
            // 7. Update Light theme (keep but not default)
            <<<'SQL'
UPDATE themes SET 
    is_default = FALSE,
    description = 'Clean, minimal light theme with neutral colors and soft shadows',
    config = JSON_OBJECT(
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
    )
WHERE slug = 'light'
SQL,
            // 8. Update Dark theme
            <<<'SQL'
UPDATE themes SET 
    is_default = FALSE,
    description = 'Elegant dark theme with deep colors and refined contrast',
    config = JSON_OBJECT(
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
    )
WHERE slug = 'dark'
SQL,
        ];
    }
};

