<?php

/**
 * Plugin API Functions
 * WordPress-like functions for plugins to use
 */

use App\Core\HookSystem;
use App\Core\PluginRegistry;

/**
 * Get database connection
 */
function get_db(): PDO
{
    return getDB();
}

/**
 * Add an action hook
 */
function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
{
    HookSystem::addAction($hook, $callback, $priority, $acceptedArgs);
}

/**
 * Execute an action hook
 */
function do_action(string $hook, ...$args): void
{
    HookSystem::doAction($hook, ...$args);
}

/**
 * Remove an action hook
 */
function remove_action(string $hook, callable $callback, int $priority = 10): bool
{
    return HookSystem::removeAction($hook, $callback, $priority);
}

/**
 * Add a filter hook
 */
function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
{
    HookSystem::addFilter($hook, $callback, $priority, $acceptedArgs);
}

/**
 * Apply a filter hook
 */
function apply_filters(string $hook, $value, ...$args)
{
    return HookSystem::applyFilters($hook, $value, ...$args);
}

/**
 * Remove a filter hook
 */
function remove_filter(string $hook, callable $callback, int $priority = 10): bool
{
    return HookSystem::removeFilter($hook, $callback, $priority);
}

/**
 * Get plugin option
 */
function get_plugin_option(string $pluginSlug, string $key, $default = null)
{
    static $registry = null;
    
    if ($registry === null) {
        $registry = new PluginRegistry(getDB());
    }
    
    return $registry->getOption($pluginSlug, $key, $default);
}

/**
 * Update plugin option
 */
function update_plugin_option(string $pluginSlug, string $key, $value): bool
{
    static $registry = null;
    
    if ($registry === null) {
        $registry = new PluginRegistry(getDB());
    }
    
    return $registry->updateOption($pluginSlug, $key, $value);
}

/**
 * Delete plugin option
 */
function delete_plugin_option(string $pluginSlug, string $key): bool
{
    static $registry = null;
    
    if ($registry === null) {
        $registry = new PluginRegistry(getDB());
    }
    
    return $registry->deleteOption($pluginSlug, $key);
}

/**
 * Check if plugin is active
 */
function is_plugin_active(string $slug): bool
{
    static $registry = null;
    
    if ($registry === null) {
        $registry = new PluginRegistry(getDB());
    }
    
    return $registry->isActive($slug);
}

/**
 * Add admin menu item
 */
function add_admin_menu_item(array $args): void
{
    $defaults = [
        'title' => '',
        'slug' => '',
        'callback' => null,
        'icon' => '📄',
        'position' => 50,
        'parent' => null,
    ];

    $args = array_merge($defaults, $args);

    add_action('admin_menu', function() use ($args) {
        // This will be handled by the admin header
        // Store menu items in a global array
        global $admin_menu_items;
        if (!isset($admin_menu_items)) {
            $admin_menu_items = [];
        }
        $admin_menu_items[] = $args;
    });
}

// ============================================
// WORDPRESS-LIKE FUNCTIONS (Aliases)
// ============================================

/**
 * Register post type
 * WordPress: register_post_type($post_type, $args)
 */
function register_post_type(string $postType, array $args = []): void
{
    \App\Core\PostTypeRegistry::register($postType, $args);
}

/**
 * Register taxonomy
 * WordPress: register_taxonomy($taxonomy, $object_type, $args)
 */
function register_taxonomy(string $taxonomy, $objectType, array $args = []): void
{
    \App\Core\TaxonomyRegistry::register($taxonomy, $objectType, $args);
}

/**
 * Register widget
 * WordPress: register_widget($widget_class)
 */
function register_widget(string $id, string $name, callable $callback, array $options = []): void
{
    \App\Core\WidgetRegistry::register($id, $name, $callback, $options);
}

/**
 * Register sidebar
 * WordPress: register_sidebar($args)
 */
function register_sidebar(array $args = []): string
{
    return \App\Core\WidgetRegistry::registerSidebar($args);
}

/**
 * Register setting
 * WordPress: register_setting($option_group, $option_name, $args)
 */
function register_setting(string $optionGroup, string $optionName, array $args = []): void
{
    \App\Core\SettingsAPI::registerSetting($optionGroup, $optionName, $args);
}

/**
 * Add settings section
 * WordPress: add_settings_section($id, $title, $callback, $page)
 */
function add_settings_section(string $id, string $title, ?callable $callback, string $page): void
{
    \App\Core\SettingsAPI::addSettingsSection($id, $title, $callback, $page);
}

/**
 * Add settings field
 * WordPress: add_settings_field($id, $title, $callback, $page, $section, $args)
 */
function add_settings_field(string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = []): void
{
    \App\Core\SettingsAPI::addSettingsField($id, $title, $callback, $page, $section, $args);
}

/**
 * Do settings fields
 * WordPress: do_settings_fields($page, $section)
 */
function do_settings_fields(string $page, string $section): void
{
    \App\Core\SettingsAPI::doSettingsFields($page, $section);
}

/**
 * Do settings sections
 * WordPress: do_settings_sections($page)
 */
function do_settings_sections(string $page): void
{
    \App\Core\SettingsAPI::doSettingsSections($page);
}

/**
 * Settings fields (for form)
 * WordPress: settings_fields($option_group)
 */
function settings_fields(string $optionGroup): void
{
    echo '<input type="hidden" name="option_page" value="' . esc_attr($optionGroup) . '">';
    wp_nonce_field($optionGroup . '-options');
}

/**
 * Nonce field
 * WordPress: wp_nonce_field($action, $name, $referer, $echo)
 */
function wp_nonce_field(string $action, string $name = '_wpnonce', bool $referer = true, bool $echo = true): string
{
    $nonce = wp_create_nonce($action);
    $field = '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($nonce) . '">';
    
    if ($referer) {
        $field .= wp_referer_field(false);
    }
    
    if ($echo) {
        echo $field;
    }
    
    return $field;
}

/**
 * Create nonce
 * WordPress: wp_create_nonce($action)
 */
function wp_create_nonce(string $action): string
{
    $user = $_SESSION['admin_email'] ?? 'anonymous';
    $token = $_SESSION['nonce_token'] ?? bin2hex(random_bytes(16));
    
    return substr(hash_hmac('sha256', $action . $user, $token), 0, 10);
}

/**
 * Referer field
 * WordPress: wp_referer_field($echo)
 */
function wp_referer_field(bool $echo = true): string
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $field = '<input type="hidden" name="_wp_http_referer" value="' . esc_attr($referer) . '">';
    
    if ($echo) {
        echo $field;
    }
    
    return $field;
}

