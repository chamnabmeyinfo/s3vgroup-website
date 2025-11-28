<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/**
 * Plugin Manager - Loads and manages plugins
 */
final class PluginManager
{
    private array $loadedPlugins = [];

    public function __construct(
        private readonly PDO $pdo,
        private readonly PluginRegistry $registry,
        private readonly HookSystem $hooks
    ) {
    }

    /**
     * Load all active plugins
     */
    public function loadActivePlugins(): void
    {
        $plugins = $this->registry->getActive();

        foreach ($plugins as $plugin) {
            $this->loadPlugin($plugin['path'], $plugin['slug']);
        }

        // Trigger plugins_loaded action
        $this->hooks->doAction('plugins_loaded');
    }

    /**
     * Load a plugin file
     */
    public function loadPlugin(string $path, string $slug): bool
    {
        if (!file_exists($path)) {
            error_log("Plugin file not found: {$path}");
            return false;
        }

        if (isset($this->loadedPlugins[$slug])) {
            return true; // Already loaded
        }

        try {
            // Define ABSPATH for plugin security
            if (!defined('ABSPATH')) {
                define('ABSPATH', dirname(__DIR__, 2) . '/');
            }

            // Load plugin file
            require_once $path;

            $this->loadedPlugins[$slug] = [
                'path' => $path,
                'loaded' => true,
            ];

            return true;
        } catch (\Throwable $e) {
            error_log("Error loading plugin {$slug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Discover plugins in plugins directory
     */
    public function discoverPlugins(string $pluginsDir = null): array
    {
        $pluginsDir = $pluginsDir ?? base_path('plugins');
        
        if (!is_dir($pluginsDir)) {
            return [];
        }

        $plugins = [];
        $dirs = glob($pluginsDir . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $pluginFile = $this->findPluginFile($dir);
            
            if ($pluginFile) {
                $metadata = $this->parsePluginHeaders($pluginFile);
                if ($metadata) {
                    $plugins[] = [
                        'path' => $pluginFile,
                        'metadata' => $metadata,
                    ];
                }
            }
        }

        return $plugins;
    }

    /**
     * Find main plugin file in directory
     */
    private function findPluginFile(string $dir): ?string
    {
        $slug = basename($dir);
        
        // Common plugin file names
        $possibleFiles = [
            $dir . '/' . $slug . '.php',
            $dir . '/' . $slug . '/' . $slug . '.php',
            $dir . '/index.php',
            $dir . '/plugin.php',
        ];

        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        // Try to find any PHP file in root
        $phpFiles = glob($dir . '/*.php');
        if (!empty($phpFiles)) {
            return $phpFiles[0];
        }

        return null;
    }

    /**
     * Parse plugin headers (WordPress-style)
     */
    private function parsePluginHeaders(string $file): ?array
    {
        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        
        $headers = [
            'name' => 'Plugin Name',
            'uri' => 'Plugin URI',
            'description' => 'Description',
            'version' => 'Version',
            'author' => 'Author',
            'authorUri' => 'Author URI',
            'license' => 'License',
            'textDomain' => 'Text Domain',
            'requires' => 'Requires',
            'tested' => 'Tested',
        ];

        $metadata = [];

        foreach ($headers as $key => $header) {
            if (preg_match('/^\s*\*\s*' . preg_quote($header, '/') . ':\s*(.+)$/mi', $content, $matches)) {
                $metadata[$key] = trim($matches[1]);
            }
        }

        // Extract slug from filename or directory
        if (empty($metadata['slug'])) {
            $metadata['slug'] = $this->extractSlug($file);
        }

        // Name is required
        if (empty($metadata['name'])) {
            return null;
        }

        return $metadata;
    }

    /**
     * Extract slug from file path
     */
    private function extractSlug(string $file): string
    {
        $basename = basename($file, '.php');
        $dir = dirname($file);
        $dirName = basename($dir);
        
        // If file is in a subdirectory, use directory name
        if ($dirName !== 'plugins') {
            return $dirName;
        }
        
        return $basename;
    }

    /**
     * Register discovered plugins
     */
    public function registerDiscoveredPlugins(): int
    {
        $plugins = $this->discoverPlugins();
        $count = 0;

        foreach ($plugins as $plugin) {
            $this->registry->register($plugin['metadata'], $plugin['path']);
            $count++;
        }

        return $count;
    }

    /**
     * Activate a plugin
     */
    public function activate(string $slug): bool
    {
        $plugin = $this->registry->getBySlug($slug);
        
        if (!$plugin) {
            throw new RuntimeException("Plugin not found: {$slug}");
        }

        // Load plugin first
        $this->loadPlugin($plugin['path'], $slug);

        // Activate in registry
        $this->registry->activate($slug);

        // Trigger activation hook
        $this->hooks->doAction('plugin_activated', $slug);

        return true;
    }

    /**
     * Deactivate a plugin
     */
    public function deactivate(string $slug): bool
    {
        if (!$this->registry->isActive($slug)) {
            return false;
        }

        // Trigger deactivation hook
        $this->hooks->doAction('plugin_deactivated', $slug);

        // Deactivate in registry
        $this->registry->deactivate($slug);

        return true;
    }

    /**
     * Get loaded plugins
     */
    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }
}

