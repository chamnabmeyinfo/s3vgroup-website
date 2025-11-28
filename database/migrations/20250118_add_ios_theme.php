<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20250118_add_ios_theme') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DELETE FROM themes WHERE id = 'theme_ios_modern'");
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            // iOS 17 / macOS Sonoma Style Theme
            <<<'SQL'
REPLACE INTO themes (id, name, slug, description, is_default, is_active, config) VALUES
('theme_ios_modern', 'iOS Modern', 'ios-modern', 'Modern iOS 17 and macOS Sonoma design with vibrant colors, SF Pro typography, and fluid animations. Features the latest Apple design language with rounded corners, soft shadows, and beautiful gradients.', FALSE, TRUE,
JSON_OBJECT(
    'colors', JSON_OBJECT(
        'background', '#F2F2F7',
        'surface', '#FFFFFF',
        'primary', '#007AFF',
        'primaryText', '#FFFFFF',
        'text', '#000000',
        'mutedText', '#8E8E93',
        'border', '#C6C6C8',
        'error', '#FF3B30',
        'success', '#34C759',
        'warning', '#FF9500',
        'accent', '#5856D6',
        'secondary', '#5AC8FA',
        'tertiary', '#FF2D55'
    ),
    'typography', JSON_OBJECT(
        'fontFamily', '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Arial, sans-serif',
        'headingScale', 1.18,
        'bodySize', 17,
        'lineHeight', 1.47,
        'fontWeightNormal', 400,
        'fontWeightMedium', 500,
        'fontWeightSemibold', 600,
        'fontWeightBold', 700,
        'letterSpacing', '-0.01em'
    ),
    'radius', JSON_OBJECT(
        'small', 8,
        'medium', 12,
        'large', 20,
        'pill', 9999
    ),
    'shadows', JSON_OBJECT(
        'card', '0 2px 8px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04)',
        'elevated', '0 8px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)',
        'subtle', '0 1px 3px rgba(0,0,0,0.06)',
        'button', '0 2px 6px rgba(0,122,255,0.2)',
        'buttonHover', '0 4px 12px rgba(0,122,255,0.3)'
    ),
    'ios', JSON_OBJECT(
        'version', 'iOS 17',
        'style', 'Modern',
        'features', JSON_ARRAY('SF Pro Typography', 'Fluid Animations', 'Rounded Corners', 'Soft Shadows', 'Vibrant Colors')
    )
))
SQL
        ];
    }
};

