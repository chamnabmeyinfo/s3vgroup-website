<?php
/**
 * Ant Elite Configuration File
 * 
 * This file contains configuration settings for the website.
 * Ant Elite (AE) System - Your Own CMS
 */

// Prevent direct access
if (!defined('AEPATH') && !defined('ABSPATH')) {
    define('AEPATH', dirname(__FILE__) . '/');
    define('ABSPATH', AEPATH);
}

// Load database configuration
if (file_exists(AEPATH . 'config/database.php')) {
    require_once AEPATH . 'config/database.php';
}

// Load site configuration
if (file_exists(AEPATH . 'config/site.php')) {
    require_once AEPATH . 'config/site.php';
}

// Ant Elite constants
if (!defined('AE_DEBUG')) {
    define('AE_DEBUG', false);
}

if (!defined('AE_DEBUG_LOG')) {
    define('AE_DEBUG_LOG', false);
}

if (!defined('AE_DEBUG_DISPLAY')) {
    define('AE_DEBUG_DISPLAY', false);
}

// Memory limit
if (!defined('AE_MEMORY_LIMIT')) {
    define('AE_MEMORY_LIMIT', '256M');
}

// Upload directory
if (!defined('AE_UPLOAD_DIR')) {
    define('AE_UPLOAD_DIR', AEPATH . 'ae-content/uploads');
}

if (!defined('AE_UPLOAD_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('AE_UPLOAD_URL', $protocol . '://' . $host . '/ae-content/uploads');
}

// Plugin directory
if (!defined('AE_PLUGIN_DIR')) {
    define('AE_PLUGIN_DIR', AEPATH . 'ae-content/plugins');
}

// Admin email (from config or default)
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', get_option('admin_email', 'admin@example.com'));
}

// Site URL
if (!defined('AE_SITEURL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('AE_SITEURL', $protocol . '://' . $host);
}

// Home URL
if (!defined('AE_HOME')) {
    define('AE_HOME', AE_SITEURL);
}

