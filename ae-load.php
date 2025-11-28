<?php
/**
 * Ant Elite Bootstrap File
 * Loads the core system and initializes everything
 * 
 * This file is loaded by all pages (frontend and admin)
 * Ant Elite (AE) System - Your Own CMS
 */

// Define AEPATH - Ant Elite constant for absolute path
if (!defined('AEPATH')) {
    define('AEPATH', dirname(__FILE__) . '/');
}

// Define ABSPATH - Alias for compatibility
if (!defined('ABSPATH')) {
    define('ABSPATH', AEPATH);
}

// Define AEINC - Ant Elite includes directory
if (!defined('AEINC')) {
    define('AEINC', 'ae-includes');
}

// Define AE_CONTENT_DIR - Ant Elite content directory
if (!defined('AE_CONTENT_DIR')) {
    define('AE_CONTENT_DIR', AEPATH . 'ae-content');
}

// Define AE_CONTENT_URL - Ant Elite content URL
if (!defined('AE_CONTENT_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('AE_CONTENT_URL', $protocol . '://' . $host . '/ae-content');
}

// Define AE_PLUGIN_DIR - Plugins directory
if (!defined('AE_PLUGIN_DIR')) {
    define('AE_PLUGIN_DIR', AE_CONTENT_DIR . '/plugins');
}

// Define AE_PLUGIN_URL - Plugins URL
if (!defined('AE_PLUGIN_URL')) {
    define('AE_PLUGIN_URL', AE_CONTENT_URL . '/plugins');
}

// Define AE_ADMIN - Admin directory
if (!defined('AE_ADMIN')) {
    define('AE_ADMIN', AEPATH . 'ae-admin');
}

// Load core helpers (from app directory)
require_once AEPATH . 'app/Support/helpers.php';
require_once AEPATH . 'app/Support/Autoloader.php';

// Load additional helper functions if available
if (file_exists(AEPATH . 'ae-includes/helpers.php')) {
    require_once AEPATH . 'ae-includes/helpers.php';
} elseif (file_exists(AEPATH . 'wp-includes/helpers.php')) {
    // Fallback to old location during migration
    require_once AEPATH . 'wp-includes/helpers.php';
} elseif (file_exists(AEPATH . 'includes/helpers.php')) {
    // Fallback to old location during migration
    require_once AEPATH . 'includes/helpers.php';
}

// Register autoloader
\App\Support\Autoloader::register();

// Load environment variables
\App\Support\Env::load(AEPATH . '.env');
\App\Support\Env::load(AEPATH . 'env.example');

// Load Ant Elite functions (check ae- first, then wp- as fallback)
if (file_exists(AEPATH . 'ae-includes/wp-functions.php')) {
    require_once AEPATH . 'ae-includes/wp-functions.php';
} elseif (file_exists(AEPATH . 'wp-includes/wp-functions.php')) {
    // Fallback to wp- during migration
    require_once AEPATH . 'wp-includes/wp-functions.php';
} elseif (file_exists(AEPATH . 'includes/wp-functions.php')) {
    // Fallback to old location
    require_once AEPATH . 'includes/wp-functions.php';
}

// Load plugin API functions (check ae- first, then wp- as fallback)
if (file_exists(AEPATH . 'ae-includes/plugin-api.php')) {
    require_once AEPATH . 'ae-includes/plugin-api.php';
} elseif (file_exists(AEPATH . 'wp-includes/plugin-api.php')) {
    // Fallback to wp- during migration
    require_once AEPATH . 'wp-includes/plugin-api.php';
} elseif (file_exists(AEPATH . 'includes/plugin-api.php')) {
    // Fallback to old location
    require_once AEPATH . 'includes/plugin-api.php';
}

// Initialize Plugin System (after plugin-api.php is loaded)
// Plugin system will be initialized by plugin-api.php if available

// Skip CacheControl for API endpoints to prevent header conflicts
if (!defined('DISABLE_CACHE_CONTROL') && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
    \App\Support\CacheControl::apply();
}

