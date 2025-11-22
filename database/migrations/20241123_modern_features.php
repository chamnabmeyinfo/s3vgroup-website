<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241123_modern_features') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DELETE FROM site_options WHERE key_name LIKE "modern_%" OR key_name LIKE "seo_%" OR key_name LIKE "design_%"');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
INSERT INTO site_options (id, key_name, value, type, group_name, label, description, priority) VALUES
('opt_100', 'enable_dark_mode', '1', 'boolean', 'design', 'Enable Dark Mode', 'Allow users to toggle dark mode', 85),
('opt_101', 'enable_animations', '1', 'boolean', 'design', 'Enable Animations', 'Enable smooth animations and transitions', 80),
('opt_102', 'font_family', 'system-ui, -apple-system, sans-serif', 'text', 'design', 'Font Family', 'Website font family (CSS)', 75),
('opt_103', 'border_radius', '8', 'number', 'design', 'Border Radius', 'Default border radius in pixels', 70),
('opt_104', 'button_style', 'rounded', 'text', 'design', 'Button Style', 'Button style: rounded, square, or pill', 65),
('opt_105', 'enable_search', '1', 'boolean', 'general', 'Enable Search', 'Enable product search functionality', 60),
('opt_106', 'seo_title', '', 'text', 'seo', 'SEO Title', 'Default page title for SEO', 100),
('opt_107', 'seo_description', '', 'textarea', 'seo', 'SEO Description', 'Default meta description for SEO', 95),
('opt_108', 'seo_keywords', '', 'text', 'seo', 'SEO Keywords', 'Comma-separated keywords', 90),
('opt_109', 'google_analytics_id', '', 'text', 'seo', 'Google Analytics ID', 'Google Analytics tracking ID (UA- or G- format)', 85),
('opt_110', 'facebook_pixel_id', '', 'text', 'seo', 'Facebook Pixel ID', 'Facebook Pixel tracking ID', 80),
('opt_111', 'enable_toast_notifications', '1', 'boolean', 'general', 'Enable Toast Notifications', 'Show toast notifications for user actions', 55)
ON DUPLICATE KEY UPDATE label=label
SQL,
        ];
    }
};

