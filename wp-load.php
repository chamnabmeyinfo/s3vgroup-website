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
require_once ABSPATH . 'app/Support/helpers.php';
require_once ABSPATH . 'app/Support/Autoloader.php';

// Load additional helper functions if available
if (file_exists(ABSPATH . 'wp-includes/helpers.php')) {
    require_once ABSPATH . 'wp-includes/helpers.php';
}

// Register autoloader
\App\Support\Autoloader::register();

// Load environment variables
\App\Support\Env::load(ABSPATH . '.env');
\App\Support\Env::load(ABSPATH . 'env.example');

// Load Ant Elite functions
if (file_exists(AEPATH . 'ae-includes/wp-functions.php')) {
    require_once AEPATH . 'ae-includes/wp-functions.php';
} elseif (file_exists(AEPATH . 'wp-includes/wp-functions.php')) {
    // Fallback to old location during migration
    require_once AEPATH . 'wp-includes/wp-functions.php';
} elseif (file_exists(AEPATH . 'includes/wp-functions.php')) {
    // Fallback to old location during migration
    require_once AEPATH . 'includes/wp-functions.php';
}

// Load plugin API functions
if (file_exists(AEPATH . 'ae-includes/plugin-api.php')) {
    require_once AEPATH . 'ae-includes/plugin-api.php';
} elseif (file_exists(AEPATH . 'wp-includes/plugin-api.php')) {
    // Fallback to old location during migration
    require_once AEPATH . 'wp-includes/plugin-api.php';
} elseif (file_exists(AEPATH . 'includes/plugin-api.php')) {
    // Fallback to old location during migration
    require_once AEPATH . 'includes/plugin-api.php';
}

// Initialize Plugin System
if (file_exists(ABSPATH . 'config/database.php')) {
    try {
        require_once ABSPATH . 'config/database.php';
        $db = getDB();
        
        $pluginRegistry = new \App\Core\PluginRegistry($db);
        $hookSystem = new \App\Core\HookSystem();
        $pluginManager = new \App\Core\PluginManager($db, $pluginRegistry, $hookSystem);
        
        // Register discovered plugins
        $pluginManager->registerDiscoveredPlugins();
        
        // Load active plugins
        $pluginManager->loadActivePlugins();
        
        // Make plugin manager globally available
        if (!isset($GLOBALS['plugin_manager'])) {
            $GLOBALS['plugin_manager'] = $pluginManager;
        }
    } catch (\Throwable $e) {
        // Silently fail if database not ready
        error_log('Plugin system initialization failed: ' . $e->getMessage());
    }
}

// Skip CacheControl for API endpoints to prevent header conflicts
if (!defined('DISABLE_CACHE_CONTROL') && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
    \App\Support\CacheControl::apply();
}

