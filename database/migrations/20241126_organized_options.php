<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241126_organized_options') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        // No rollback needed - just reorganizing groups
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
UPDATE site_options SET group_name = 'homepage_design' WHERE key_name IN (
    'homepage_hero_title', 'homepage_hero_subtitle'
);

UPDATE site_options SET group_name = 'typography_fonts' WHERE key_name IN (
    'font_family', 'design_font_family', 'design_font_size_base', 'design_font_weight_normal', 
    'design_font_weight_bold', 'design_line_height', 'design_heading_font'
);

UPDATE site_options SET group_name = 'colors_theme' WHERE key_name IN (
    'primary_color', 'secondary_color', 'accent_color', 'header_background', 
    'footer_background', 'design_link_color', 'design_link_hover_color'
);

UPDATE site_options SET group_name = 'layout_spacing' WHERE key_name IN (
    'design_border_radius', 'design_spacing_unit', 'design_container_width', 
    'design_header_height', 'design_footer_height'
);

UPDATE site_options SET group_name = 'language_localization' WHERE key_name IN (
    'site_language', 'site_locale', 'date_format', 'time_format', 'currency_symbol', 'currency_code'
);

UPDATE site_options SET group_name = 'seo_analytics' WHERE key_name IN (
    'seo_title', 'seo_description', 'seo_keywords', 'seo_og_image', 'seo_twitter_card',
    'google_analytics_id', 'facebook_pixel_id'
);

UPDATE site_options SET group_name = 'social_media' WHERE key_name IN (
    'facebook_url', 'linkedin_url', 'twitter_url', 'youtube_url'
);

UPDATE site_options SET group_name = 'contact_info' WHERE key_name IN (
    'contact_email', 'contact_phone', 'contact_address', 'business_hours'
);

UPDATE site_options SET group_name = 'email_settings' WHERE key_name IN (
    'email_from_name', 'email_from_address', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password'
);

UPDATE site_options SET group_name = 'performance' WHERE key_name IN (
    'enable_lazy_loading', 'enable_caching', 'cache_duration', 'enable_compression'
);

UPDATE site_options SET group_name = 'features' WHERE key_name IN (
    'enable_dark_mode', 'enable_search', 'enable_animations', 'enable_toast_notifications',
    'enable_newsletter', 'enable_social_sharing', 'enable_blog', 'enable_testimonials'
);

UPDATE site_options SET group_name = 'components' WHERE key_name IN (
    'design_button_style', 'design_button_padding_x', 'design_button_padding_y',
    'design_card_shadow', 'design_background_pattern', 'design_background_image', 'design_background_overlay'
);

UPDATE site_options SET group_name = 'advanced' WHERE key_name IN (
    'design_custom_css', 'custom_js_head', 'custom_js_footer'
);
SQL,
            <<<'SQL'
INSERT INTO site_options (id, key_name, value, type, group_name, label, description, priority) VALUES
('opt_400', 'site_language', 'en', 'text', 'language_localization', 'Site Language', 'Default site language (en, kh, etc.)', 100),
('opt_401', 'site_locale', 'en_US', 'text', 'language_localization', 'Site Locale', 'Locale for formatting (en_US, km_KH, etc.)', 95),
('opt_402', 'date_format', 'M d, Y', 'text', 'language_localization', 'Date Format', 'Date display format (e.g., M d, Y)', 90),
('opt_403', 'time_format', 'g:i A', 'text', 'language_localization', 'Time Format', 'Time display format (e.g., g:i A)', 85),
('opt_404', 'currency_symbol', '$', 'text', 'language_localization', 'Currency Symbol', 'Currency symbol to display', 80),
('opt_405', 'currency_code', 'USD', 'text', 'language_localization', 'Currency Code', 'Currency code (USD, KHR, etc.)', 75),
('opt_406', 'smtp_host', '', 'text', 'email_settings', 'SMTP Host', 'SMTP server hostname', 100),
('opt_407', 'smtp_port', '587', 'text', 'email_settings', 'SMTP Port', 'SMTP server port', 95),
('opt_408', 'smtp_username', '', 'text', 'email_settings', 'SMTP Username', 'SMTP authentication username', 90),
('opt_409', 'smtp_password', '', 'text', 'email_settings', 'SMTP Password', 'SMTP authentication password', 85),
('opt_410', 'cache_duration', '3600', 'number', 'performance', 'Cache Duration (seconds)', 'How long to cache pages (in seconds)', 95),
('opt_411', 'enable_compression', '1', 'boolean', 'performance', 'Enable Compression', 'Enable GZIP compression for better performance', 90),
('opt_412', 'custom_js_head', '', 'textarea', 'advanced', 'Custom JavaScript (Head)', 'Custom JavaScript code to add in &lt;head&gt;', 100),
('opt_413', 'custom_js_footer', '', 'textarea', 'advanced', 'Custom JavaScript (Footer)', 'Custom JavaScript code to add before &lt;/body&gt;', 95)
ON DUPLICATE KEY UPDATE label=label
SQL,
        ];
    }
};

