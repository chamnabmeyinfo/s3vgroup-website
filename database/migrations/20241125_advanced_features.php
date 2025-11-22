<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241125_advanced_features') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS testimonials');
        $pdo->exec('DROP TABLE IF EXISTS newsletter_subscribers');
        $pdo->exec('DROP TABLE IF EXISTS blog_posts');
        $pdo->exec('DROP TABLE IF EXISTS homepage_widgets');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS testimonials (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    position VARCHAR(255),
    content TEXT NOT NULL,
    rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    avatar VARCHAR(500),
    featured BOOLEAN DEFAULT FALSE,
    priority INT DEFAULT 0,
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id VARCHAR(255) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    status ENUM('ACTIVE', 'UNSUBSCRIBED', 'BOUNCED') DEFAULT 'ACTIVE',
    subscribedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribedAt TIMESTAMP NULL,
    source VARCHAR(255),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS blog_posts (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content TEXT,
    featured_image VARCHAR(500),
    author_name VARCHAR(255),
    author_email VARCHAR(255),
    category VARCHAR(100),
    tags JSON,
    views INT DEFAULT 0,
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    publishedAt TIMESTAMP NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published (publishedAt),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS homepage_widgets (
    id VARCHAR(255) PRIMARY KEY,
    widget_type VARCHAR(50) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    config JSON,
    priority INT DEFAULT 0,
    visible BOOLEAN DEFAULT TRUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (widget_type),
    INDEX idx_priority (priority),
    INDEX idx_visible (visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
INSERT INTO site_options (id, key_name, value, type, group_name, label, description, priority) VALUES
('opt_300', 'enable_newsletter', '1', 'boolean', 'general', 'Enable Newsletter', 'Enable newsletter subscription feature', 50),
('opt_301', 'newsletter_api_key', '', 'text', 'general', 'Newsletter API Key', 'API key for newsletter service (Mailchimp, etc.)', 45),
('opt_302', 'enable_social_sharing', '1', 'boolean', 'general', 'Enable Social Sharing', 'Show social sharing buttons on content', 40),
('opt_303', 'enable_lazy_loading', '1', 'boolean', 'general', 'Enable Lazy Loading', 'Lazy load images for better performance', 35),
('opt_304', 'enable_caching', '0', 'boolean', 'general', 'Enable Caching', 'Enable page caching for better performance', 30),
('opt_305', 'seo_og_image', '', 'image', 'seo', 'Open Graph Image', 'Default image for social media sharing', 100),
('opt_306', 'seo_twitter_card', 'summary_large_image', 'text', 'seo', 'Twitter Card Type', 'Twitter card type', 95),
('opt_307', 'email_from_name', '', 'text', 'general', 'Email From Name', 'Name shown in email sender', 90),
('opt_308', 'email_from_address', '', 'text', 'general', 'Email From Address', 'Email address for sending emails', 85),
('opt_309', 'enable_blog', '1', 'boolean', 'general', 'Enable Blog', 'Enable blog/news section', 80),
('opt_310', 'blog_posts_per_page', '10', 'number', 'general', 'Blog Posts Per Page', 'Number of posts per page', 75),
('opt_311', 'enable_testimonials', '1', 'boolean', 'general', 'Enable Testimonials', 'Enable testimonials/reviews section', 70),
('opt_312', 'testimonials_per_page', '6', 'number', 'general', 'Testimonials Per Page', 'Number of testimonials per page', 65)
ON DUPLICATE KEY UPDATE label=label
SQL,
        ];
    }
};

