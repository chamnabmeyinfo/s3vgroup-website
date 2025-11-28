<?php
/**
 * WordPress-like Configuration File
 * 
 * This file contains configuration settings for the website.
 * Similar to WordPress wp-config.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Load database configuration
if (file_exists(ABSPATH . 'config/database.php')) {
    require_once ABSPATH . 'config/database.php';
}

// Load site configuration
if (file_exists(ABSPATH . 'config/site.php')) {
    require_once ABSPATH . 'config/site.php';
}

// WordPress-like constants
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}

if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', false);
}

if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', false);
}

// Memory limit
if (!defined('WP_MEMORY_LIMIT')) {
    define('WP_MEMORY_LIMIT', '256M');
}

// Upload directory
if (!defined('WP_UPLOAD_DIR')) {
    define('WP_UPLOAD_DIR', ABSPATH . 'wp-content/uploads');
}

if (!defined('WP_UPLOAD_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('WP_UPLOAD_URL', $protocol . '://' . $host . '/wp-content/uploads');
}

// Plugin directory
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins');
}

// Admin email (from config or default)
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', get_option('admin_email', 'admin@example.com'));
}

// Site URL
if (!defined('WP_SITEURL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('WP_SITEURL', $protocol . '://' . $host);
}

// Home URL
if (!defined('WP_HOME')) {
    define('WP_HOME', WP_SITEURL);
}

