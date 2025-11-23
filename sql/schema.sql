-- S3vgroup Website Database Schema
-- MySQL/MariaDB compatible for cPanel hosting
-- Warehouse and Factory Equipment E-commerce Platform

-- Create database (run this in cPanel MySQL Database section first)
-- CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    priority INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    sku VARCHAR(255),
    summary TEXT,
    description TEXT,
    specs JSON,
    heroImage VARCHAR(500),
    price DECIMAL(12, 2),
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    highlights JSON,
    categoryId VARCHAR(255) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_category (categoryId),
    INDEX idx_status (status),
    FOREIGN KEY (categoryId) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Media Table
CREATE TABLE IF NOT EXISTS product_media (
    id VARCHAR(255) PRIMARY KEY,
    url VARCHAR(500) NOT NULL,
    alt VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    productId VARCHAR(255) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (productId),
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Tags Table
CREATE TABLE IF NOT EXISTS product_tags (
    id VARCHAR(255) PRIMARY KEY,
    label VARCHAR(255) NOT NULL,
    productId VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_product_tag (productId, label),
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quote Requests Table
CREATE TABLE IF NOT EXISTS quote_requests (
    id VARCHAR(255) PRIMARY KEY,
    companyName VARCHAR(255) NOT NULL,
    contactName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT,
    items JSON,
    status ENUM('NEW', 'IN_PROGRESS', 'RESOLVED', 'CLOSED') DEFAULT 'NEW',
    source VARCHAR(255),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team Members Table
CREATE TABLE IF NOT EXISTS team_members (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    bio TEXT,
    photo VARCHAR(500),
    email VARCHAR(255),
    phone VARCHAR(50),
    linkedin VARCHAR(500),
    priority INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolio Projects Table
CREATE TABLE IF NOT EXISTS portfolio_projects (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    industry VARCHAR(255) NOT NULL,
    client VARCHAR(255),
    description TEXT,
    challenge TEXT,
    solution TEXT,
    results TEXT,
    heroImage VARCHAR(500),
    images JSON,
    completionDate DATE,
    status ENUM('DRAFT', 'PUBLISHED', 'FEATURED', 'ARCHIVED') DEFAULT 'DRAFT',
    priority INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Options Table (REQUIRED - used by option() function)
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

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    position VARCHAR(255),
    testimonial TEXT NOT NULL,
    rating INT DEFAULT 5,
    photo VARCHAR(500),
    featured BOOLEAN DEFAULT FALSE,
    priority INT DEFAULT 0,
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id VARCHAR(255) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    status ENUM('ACTIVE', 'UNSUBSCRIBED') DEFAULT 'ACTIVE',
    source VARCHAR(255),
    subscribedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribedAt TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sliders Table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Company Story Table
CREATE TABLE IF NOT EXISTS company_story (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    heroImage VARCHAR(500),
    introduction TEXT,
    history TEXT,
    mission TEXT,
    vision TEXT,
    `values` JSON,
    milestones JSON,
    achievements TEXT,
    status ENUM('DRAFT', 'PUBLISHED') DEFAULT 'DRAFT',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CEO Message Table
CREATE TABLE IF NOT EXISTS ceo_message (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL DEFAULT 'Message from CEO',
    message TEXT NOT NULL,
    photo VARCHAR(500),
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    signature VARCHAR(500),
    displayOrder INT DEFAULT 0,
    status ENUM('DRAFT', 'PUBLISHED') DEFAULT 'DRAFT',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order (displayOrder)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pages Table
CREATE TABLE IF NOT EXISTS pages (
    id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    excerpt TEXT,
    template VARCHAR(100) DEFAULT 'default',
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(255),
    status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
    priority INT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homepage Sections Table
CREATE TABLE IF NOT EXISTS homepage_sections (
    id VARCHAR(255) PRIMARY KEY,
    page_id VARCHAR(255) NULL,
    section_type VARCHAR(50) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    config JSON,
    order_index INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page (page_id),
    INDEX idx_order (order_index),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data (Optional - for testing)
-- Insert sample categories for warehouse and factory equipment
INSERT INTO categories (id, name, slug, description, priority) VALUES
('cat_001', 'Forklifts', 'forklifts', 'Electric, diesel, and gas forklifts for material handling', 100),
('cat_002', 'Material Handling', 'material-handling', 'Pallet jacks, hand trucks, and lifting equipment', 90),
('cat_003', 'Storage Solutions', 'storage-solutions', 'Shelving, racks, and warehouse storage systems', 85),
('cat_004', 'Industrial Equipment', 'industrial-equipment', 'Conveyors, dock equipment, and factory machinery', 80),
('cat_005', 'Safety Equipment', 'safety-equipment', 'Safety barriers, signage, and protective equipment', 75),
('cat_006', 'Warehouse Accessories', 'warehouse-accessories', 'Bins, containers, and warehouse organization tools', 70)
ON DUPLICATE KEY UPDATE name=name;

-- Insert Default Site Options (REQUIRED - used by option() function)
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
('opt_600', 'enable_homepage_builder', '0', 'boolean', 'general', 'Enable Homepage Builder', 'Use drag-and-drop homepage builder instead of default sections', 50),

-- Design & Colors
('opt_005', 'primary_color', '#0b3a63', 'color', 'design', 'Primary Color', 'Main brand color', 100),
('opt_006', 'secondary_color', '#1a5a8a', 'color', 'design', 'Secondary Color', 'Secondary brand color', 95),
('opt_007', 'accent_color', '#fa4f26', 'color', 'design', 'Accent Color', 'Accent/CTA color', 90),
('opt_008', 'header_background', '#ffffff', 'color', 'design', 'Header Background', 'Header background color', 85),
('opt_009', 'footer_background', '#0b3a63', 'color', 'design', 'Footer Background', 'Footer background color', 80),
('opt_200', 'design_font_family', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif', 'text', 'design', 'Font Family', 'Main font family for the website', 100),
('opt_201', 'design_font_size_base', '16', 'number', 'design', 'Base Font Size (px)', 'Base font size in pixels', 95),
('opt_202', 'design_font_weight_normal', '400', 'number', 'design', 'Normal Font Weight', 'Font weight for normal text (100-900)', 90),
('opt_203', 'design_font_weight_bold', '700', 'number', 'design', 'Bold Font Weight', 'Font weight for bold text (100-900)', 85),
('opt_204', 'design_line_height', '1.6', 'number', 'design', 'Line Height', 'Line height multiplier', 80),
('opt_206', 'design_border_radius', '8', 'number', 'design', 'Border Radius (px)', 'Default border radius in pixels', 70),
('opt_207', 'design_button_style', 'rounded', 'text', 'design', 'Button Style', 'Button style: rounded, square, or pill', 65),
('opt_211', 'design_spacing_unit', '8', 'number', 'design', 'Spacing Unit (px)', 'Base spacing unit for margins and padding', 45),
('opt_212', 'design_container_width', '1280', 'number', 'design', 'Container Max Width (px)', 'Maximum container width', 40),

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

-- Footer
('opt_020', 'footer_copyright', '© 2025 S3V Group. All rights reserved.', 'text', 'footer', 'Copyright Text', 'Footer copyright notice', 100),

-- SEO & Analytics
('opt_106', 'seo_title', '', 'text', 'seo', 'SEO Title', 'Default page title for SEO', 100),
('opt_107', 'seo_description', '', 'textarea', 'seo', 'SEO Description', 'Default meta description for SEO', 95),
('opt_108', 'seo_keywords', '', 'text', 'seo', 'SEO Keywords', 'Comma-separated keywords', 90),
('opt_109', 'google_analytics_id', '', 'text', 'seo', 'Google Analytics ID', 'Google Analytics tracking ID (UA- or G- format)', 85),
('opt_110', 'facebook_pixel_id', '', 'text', 'seo', 'Facebook Pixel ID', 'Facebook Pixel tracking ID', 80)
ON DUPLICATE KEY UPDATE label=label;
