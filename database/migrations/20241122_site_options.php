<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241122_site_options') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS site_options');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS site_options (
    id VARCHAR(255) PRIMARY KEY,
    key_name VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    type ENUM('text', 'textarea', 'number', 'boolean', 'json', 'color', 'image', 'url') DEFAULT 'text',
    group_name VARCHAR(100) DEFAULT 'general',
    label VARCHAR(255),
    description TEXT,
    priority INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (key_name),
    INDEX idx_group (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
INSERT INTO site_options (id, key_name, value, type, group_name, label, description, priority) VALUES
('opt_001', 'site_name', 'S3V Group', 'text', 'general', 'Site Name', 'The name of your website', 100),
('opt_002', 'site_tagline', 'Your Business Solutions', 'text', 'general', 'Site Tagline', 'Short tagline or slogan', 95),
('opt_003', 'site_logo', '', 'image', 'general', 'Site Logo', 'Main logo image URL', 90),
('opt_004', 'site_favicon', '', 'image', 'general', 'Favicon', 'Favicon icon URL', 85),
('opt_005', 'primary_color', '#0b3a63', 'color', 'design', 'Primary Color', 'Main brand color', 100),
('opt_006', 'secondary_color', '#1a5a8a', 'color', 'design', 'Secondary Color', 'Secondary brand color', 95),
('opt_007', 'accent_color', '#fa4f26', 'color', 'design', 'Accent Color', 'Accent/CTA color', 90),
('opt_008', 'header_background', '#ffffff', 'color', 'design', 'Header Background', 'Header background color', 85),
('opt_009', 'footer_background', '#0b3a63', 'color', 'design', 'Footer Background', 'Footer background color', 80),
('opt_010', 'contact_email', 'Cambodiainfo@s3vtgroup.com.kh', 'text', 'contact', 'Contact Email', 'Main contact email address', 100),
('opt_011', 'contact_phone', '+855 23 123 456', 'text', 'contact', 'Contact Phone', 'Main contact phone number', 95),
('opt_012', 'contact_address', 'Phnom Penh, Cambodia', 'text', 'contact', 'Address', 'Business address', 90),
('opt_013', 'business_hours', 'Mon-Fri: 8AM-6PM, Sat: 9AM-5PM', 'text', 'contact', 'Business Hours', 'Operating hours', 85),
('opt_014', 'facebook_url', 'https://web.facebook.com/s3vgroupcambodia/', 'url', 'social', 'Facebook URL', 'Facebook page URL', 100),
('opt_015', 'linkedin_url', '', 'url', 'social', 'LinkedIn URL', 'LinkedIn profile URL', 95),
('opt_016', 'twitter_url', '', 'url', 'social', 'Twitter URL', 'Twitter profile URL', 90),
('opt_017', 'youtube_url', '', 'url', 'social', 'YouTube URL', 'YouTube channel URL', 85),
('opt_018', 'homepage_hero_title', 'Warehouse & Factory Equipment Solutions', 'textarea', 'homepage', 'Hero Title', 'Main hero section title', 100),
('opt_019', 'homepage_hero_subtitle', 'Leading supplier of industrial equipment in Cambodia. Forklifts, material handling systems, storage solutions, and warehouse equipment.', 'textarea', 'homepage', 'Hero Subtitle', 'Hero section subtitle/description', 95),
('opt_020', 'footer_copyright', '© 2025 S3V Group. All rights reserved.', 'text', 'footer', 'Copyright Text', 'Footer copyright notice', 100)
ON DUPLICATE KEY UPDATE label=label
SQL,
        ];
    }
};

