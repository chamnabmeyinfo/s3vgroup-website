<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241127_frontend_features') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS sliders');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS sliders (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    description TEXT,
    image_url VARCHAR(500) NOT NULL,
    link_url VARCHAR(500),
    link_text VARCHAR(255),
    button_color VARCHAR(50) DEFAULT '#0b3a63',
    priority INT DEFAULT 0,
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<'SQL'
INSERT INTO site_options (id, key_name, value, type, group_name, label, description, priority) VALUES
('opt_500', 'enable_hero_slider', '1', 'boolean', 'homepage_design', 'Enable Hero Slider', 'Enable hero slider/carousel on homepage', 100),
('opt_501', 'slider_autoplay', '1', 'boolean', 'homepage_design', 'Slider Autoplay', 'Automatically advance slides', 95),
('opt_502', 'slider_autoplay_speed', '5000', 'number', 'homepage_design', 'Slider Autoplay Speed (ms)', 'Time between slides in milliseconds', 90),
('opt_503', 'slider_transition', 'fade', 'text', 'homepage_design', 'Slider Transition', 'Transition effect: fade, slide, or zoom', 85),
('opt_504', 'enable_parallax', '0', 'boolean', 'homepage_design', 'Enable Parallax', 'Enable parallax scrolling effects', 80),
('opt_505', 'enable_smooth_scroll', '1', 'boolean', 'homepage_design', 'Enable Smooth Scroll', 'Smooth scrolling behavior', 75),
('opt_506', 'animation_speed', 'normal', 'text', 'homepage_design', 'Animation Speed', 'Animation speed: slow, normal, or fast', 70),
('opt_507', 'enable_loading_animation', '1', 'boolean', 'homepage_design', 'Enable Loading Animation', 'Show loading animation on page load', 65)
ON DUPLICATE KEY UPDATE label=label
SQL,
        ];
    }
};

