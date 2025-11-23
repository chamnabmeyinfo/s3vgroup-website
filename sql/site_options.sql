-- Site Options Table and Default Data
-- This file creates the site_options table and inserts all default options
-- Import this file if site_options table is missing

-- Site Options Table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Site Options
INSERT INTO site_options (id, key_name, value, type, group_name, label, description, priority) VALUES
-- General Settings
('opt_001', 'site_name', 'S3V Group', 'text', 'general', 'Site Name', 'The name of your website', 100),
('opt_002', 'site_tagline', 'Your Business Solutions', 'text', 'general', 'Site Tagline', 'Short tagline or slogan', 95),
('opt_003', 'site_logo', '', 'image', 'general', 'Site Logo', 'Main logo image URL', 90),
('opt_004', 'site_favicon', '', 'image', 'general', 'Favicon', 'Favicon icon URL', 85),
('opt_100', 'enable_dark_mode', '1', 'boolean', 'general', 'Enable Dark Mode', 'Allow users to toggle dark mode', 85),
('opt_101', 'enable_animations', '1', 'boolean', 'general', 'Enable Animations', 'Enable smooth animations and transitions', 80),
('opt_105', 'enable_search', '1', 'boolean', 'general', 'Enable Search', 'Enable product search functionality', 60),
('opt_111', 'enable_toast_notifications', '1', 'boolean', 'general', 'Enable Toast Notifications', 'Show toast notifications for user actions', 55),
('opt_300', 'enable_newsletter', '1', 'boolean', 'general', 'Enable Newsletter', 'Enable newsletter subscription feature', 50),
('opt_301', 'newsletter_api_key', '', 'text', 'general', 'Newsletter API Key', 'API key for newsletter service (Mailchimp, etc.)', 45),
('opt_302', 'enable_social_sharing', '1', 'boolean', 'general', 'Enable Social Sharing', 'Show social sharing buttons on content', 40),
('opt_303', 'enable_lazy_loading', '1', 'boolean', 'general', 'Enable Lazy Loading', 'Lazy load images for better performance', 35),
('opt_304', 'enable_caching', '0', 'boolean', 'general', 'Enable Caching', 'Enable page caching for better performance', 30),
('opt_309', 'enable_blog', '1', 'boolean', 'general', 'Enable Blog', 'Enable blog/news section', 80),
('opt_310', 'blog_posts_per_page', '10', 'number', 'general', 'Blog Posts Per Page', 'Number of posts per page', 75),
('opt_311', 'enable_testimonials', '1', 'boolean', 'general', 'Enable Testimonials', 'Enable testimonials/reviews section', 70),
('opt_312', 'testimonials_per_page', '6', 'number', 'general', 'Testimonials Per Page', 'Number of testimonials per page', 65),

-- Design & Colors
('opt_005', 'primary_color', '#0b3a63', 'color', 'design', 'Primary Color', 'Main brand color', 100),
('opt_006', 'secondary_color', '#1a5a8a', 'color', 'design', 'Secondary Color', 'Secondary brand color', 95),
('opt_007', 'accent_color', '#fa4f26', 'color', 'design', 'Accent Color', 'Accent/CTA color', 90),
('opt_008', 'header_background', '#ffffff', 'color', 'design', 'Header Background', 'Header background color', 85),
('opt_009', 'footer_background', '#0b3a63', 'color', 'design', 'Footer Background', 'Footer background color', 80),
('opt_102', 'font_family', 'system-ui, -apple-system, sans-serif', 'text', 'design', 'Font Family', 'Website font family (CSS)', 75),
('opt_103', 'border_radius', '8', 'number', 'design', 'Border Radius', 'Default border radius in pixels', 70),
('opt_104', 'button_style', 'rounded', 'text', 'design', 'Button Style', 'Button style: rounded, square, or pill', 65),
('opt_200', 'design_font_family', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif', 'text', 'design', 'Font Family', 'Main font family for the website', 100),
('opt_201', 'design_font_size_base', '16', 'number', 'design', 'Base Font Size (px)', 'Base font size in pixels', 95),
('opt_202', 'design_font_weight_normal', '400', 'number', 'design', 'Normal Font Weight', 'Font weight for normal text (100-900)', 90),
('opt_203', 'design_font_weight_bold', '700', 'number', 'design', 'Bold Font Weight', 'Font weight for bold text (100-900)', 85),
('opt_204', 'design_line_height', '1.6', 'number', 'design', 'Line Height', 'Line height multiplier', 80),
('opt_205', 'design_heading_font', '', 'text', 'design', 'Heading Font', 'Separate font family for headings (optional)', 75),
('opt_206', 'design_border_radius', '8', 'number', 'design', 'Border Radius (px)', 'Default border radius in pixels', 70),
('opt_207', 'design_button_style', 'rounded', 'text', 'design', 'Button Style', 'Button style: rounded, square, or pill', 65),
('opt_208', 'design_button_padding_x', '24', 'number', 'design', 'Button Padding X (px)', 'Horizontal button padding', 60),
('opt_209', 'design_button_padding_y', '12', 'number', 'design', 'Button Padding Y (px)', 'Vertical button padding', 55),
('opt_210', 'design_card_shadow', 'medium', 'text', 'design', 'Card Shadow', 'Card shadow: none, small, medium, or large', 50),
('opt_211', 'design_spacing_unit', '8', 'number', 'design', 'Spacing Unit (px)', 'Base spacing unit for margins and padding', 45),
('opt_212', 'design_container_width', '1280', 'number', 'design', 'Container Max Width (px)', 'Maximum container width', 40),
('opt_213', 'design_header_height', '64', 'number', 'design', 'Header Height (px)', 'Header height in pixels', 35),
('opt_214', 'design_footer_height', 'auto', 'text', 'design', 'Footer Height', 'Footer height (auto or specific px)', 30),
('opt_215', 'design_link_color', '', 'color', 'design', 'Link Color', 'Color for links (empty for primary color)', 25),
('opt_216', 'design_link_hover_color', '', 'color', 'design', 'Link Hover Color', 'Color for links on hover', 20),
('opt_217', 'design_background_pattern', 'none', 'text', 'design', 'Background Pattern', 'Background pattern: none, dots, grid, or lines', 15),
('opt_218', 'design_background_image', '', 'image', 'design', 'Background Image', 'Optional background image URL', 10),
('opt_219', 'design_background_overlay', '0', 'number', 'design', 'Background Overlay Opacity', 'Background overlay opacity (0-100)', 5),
('opt_220', 'design_custom_css', '', 'textarea', 'design', 'Custom CSS', 'Add custom CSS code', 1),

-- Contact Information
('opt_010', 'contact_email', 'Cambodiainfo@s3vtgroup.com.kh', 'text', 'contact', 'Contact Email', 'Main contact email address', 100),
('opt_011', 'contact_phone', '+855 23 123 456', 'text', 'contact', 'Contact Phone', 'Main contact phone number', 95),
('opt_012', 'contact_address', 'Phnom Penh, Cambodia', 'text', 'contact', 'Address', 'Business address', 90),
('opt_013', 'business_hours', 'Mon-Fri: 8AM-6PM, Sat: 9AM-5PM', 'text', 'contact', 'Business Hours', 'Operating hours', 85),

-- Social Media
('opt_014', 'facebook_url', 'https://web.facebook.com/s3vgroupcambodia/', 'url', 'social', 'Facebook URL', 'Facebook page URL', 100),
('opt_015', 'linkedin_url', '', 'url', 'social', 'LinkedIn URL', 'LinkedIn profile URL', 95),
('opt_016', 'twitter_url', '', 'url', 'social', 'Twitter URL', 'Twitter profile URL', 90),
('opt_017', 'youtube_url', '', 'url', 'social', 'YouTube URL', 'YouTube channel URL', 85),

-- Homepage
('opt_018', 'homepage_hero_title', 'Warehouse & Factory Equipment Solutions', 'textarea', 'homepage', 'Hero Title', 'Main hero section title', 100),
('opt_019', 'homepage_hero_subtitle', 'Leading supplier of industrial equipment in Cambodia. Forklifts, material handling systems, storage solutions, and warehouse equipment.', 'textarea', 'homepage', 'Hero Subtitle', 'Hero section subtitle/description', 95),
('opt_500', 'enable_hero_slider', '1', 'boolean', 'homepage', 'Enable Hero Slider', 'Enable hero slider/carousel on homepage', 100),
('opt_501', 'slider_autoplay', '1', 'boolean', 'homepage', 'Slider Autoplay', 'Automatically advance slides', 95),
('opt_502', 'slider_autoplay_speed', '5000', 'number', 'homepage', 'Slider Autoplay Speed (ms)', 'Time between slides in milliseconds', 90),
('opt_503', 'slider_transition', 'fade', 'text', 'homepage', 'Slider Transition', 'Transition effect: fade, slide, or zoom', 85),
('opt_504', 'enable_parallax', '0', 'boolean', 'homepage', 'Enable Parallax', 'Enable parallax scrolling effects', 80),
('opt_505', 'enable_smooth_scroll', '1', 'boolean', 'homepage', 'Enable Smooth Scroll', 'Smooth scrolling behavior', 75),
('opt_506', 'animation_speed', 'normal', 'text', 'homepage', 'Animation Speed', 'Animation speed: slow, normal, or fast', 70),
('opt_507', 'enable_loading_animation', '1', 'boolean', 'homepage', 'Enable Loading Animation', 'Show loading animation on page load', 65),

-- Footer
('opt_020', 'footer_copyright', '© 2025 S3V Group. All rights reserved.', 'text', 'footer', 'Copyright Text', 'Footer copyright notice', 100),

-- SEO & Analytics
('opt_106', 'seo_title', '', 'text', 'seo', 'SEO Title', 'Default page title for SEO', 100),
('opt_107', 'seo_description', '', 'textarea', 'seo', 'SEO Description', 'Default meta description for SEO', 95),
('opt_108', 'seo_keywords', '', 'text', 'seo', 'SEO Keywords', 'Comma-separated keywords', 90),
('opt_109', 'google_analytics_id', '', 'text', 'seo', 'Google Analytics ID', 'Google Analytics tracking ID (UA- or G- format)', 85),
('opt_110', 'facebook_pixel_id', '', 'text', 'seo', 'Facebook Pixel ID', 'Facebook Pixel tracking ID', 80),
('opt_305', 'seo_og_image', '', 'image', 'seo', 'Open Graph Image', 'Default image for social media sharing', 100),
('opt_306', 'seo_twitter_card', 'summary_large_image', 'text', 'seo', 'Twitter Card Type', 'Twitter card type', 95),

-- Language & Localization
('opt_400', 'site_language', 'en', 'text', 'language', 'Site Language', 'Default site language (en, kh, etc.)', 100),
('opt_401', 'site_locale', 'en_US', 'text', 'language', 'Site Locale', 'Locale for formatting (en_US, km_KH, etc.)', 95),
('opt_402', 'date_format', 'M d, Y', 'text', 'language', 'Date Format', 'Date display format (e.g., M d, Y)', 90),
('opt_403', 'time_format', 'g:i A', 'text', 'language', 'Time Format', 'Time display format (e.g., g:i A)', 85),
('opt_404', 'currency_symbol', '$', 'text', 'language', 'Currency Symbol', 'Currency symbol to display', 80),
('opt_405', 'currency_code', 'USD', 'text', 'language', 'Currency Code', 'Currency code (USD, KHR, etc.)', 75),

-- Email Settings
('opt_307', 'email_from_name', '', 'text', 'email', 'Email From Name', 'Name shown in email sender', 90),
('opt_308', 'email_from_address', '', 'text', 'email', 'Email From Address', 'Email address for sending emails', 85),
('opt_406', 'smtp_host', '', 'text', 'email', 'SMTP Host', 'SMTP server hostname', 100),
('opt_407', 'smtp_port', '587', 'text', 'email', 'SMTP Port', 'SMTP server port', 95),
('opt_408', 'smtp_username', '', 'text', 'email', 'SMTP Username', 'SMTP authentication username', 90),
('opt_409', 'smtp_password', '', 'text', 'email', 'SMTP Password', 'SMTP authentication password', 85),

-- Performance
('opt_410', 'cache_duration', '3600', 'number', 'performance', 'Cache Duration (seconds)', 'How long to cache pages (in seconds)', 95),
('opt_411', 'enable_compression', '1', 'boolean', 'performance', 'Enable Compression', 'Enable GZIP compression for better performance', 90),

-- Advanced
('opt_412', 'custom_js_head', '', 'textarea', 'advanced', 'Custom JavaScript (Head)', 'Custom JavaScript code to add in <head>', 100),
('opt_413', 'custom_js_footer', '', 'textarea', 'advanced', 'Custom JavaScript (Footer)', 'Custom JavaScript code to add before </body>', 95),

-- Homepage Builder
('opt_600', 'enable_homepage_builder', '0', 'boolean', 'general', 'Enable Homepage Builder', 'Use drag-and-drop homepage builder instead of default sections', 50)
ON DUPLICATE KEY UPDATE label=label;

