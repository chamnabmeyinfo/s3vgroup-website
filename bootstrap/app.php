<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/app/Support/helpers.php';
require_once base_path('app/Support/Autoloader.php');

// Load additional helper functions if available
if (file_exists(base_path('includes/helpers.php'))) {
    require_once base_path('includes/helpers.php');
}

\App\Support\Autoloader::register();
\App\Support\Env::load(base_path('.env'));
\App\Support\Env::load(base_path('env.example'));

// Load WordPress-like functions
if (file_exists(base_path('includes/wp-functions.php'))) {
    require_once base_path('includes/wp-functions.php');
}

// Load plugin API functions
if (file_exists(base_path('includes/plugin-api.php'))) {
    require_once base_path('includes/plugin-api.php');
}

// Initialize Plugin System
if (file_exists(base_path('config/database.php'))) {
    try {
        require_once base_path('config/database.php');
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

