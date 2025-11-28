# 🔌 WordPress-Like Plugin System Architecture

## 🎯 Goal

Build a smart, extensible system with plugin architecture similar to WordPress, allowing developers to extend functionality through plugins.

---

## 🏗️ Architecture Overview

### Core Components

1. **Plugin Manager** - Loads, activates, deactivates plugins
2. **Hook System** - Actions and Filters (like WordPress)
3. **Plugin API** - Functions for plugins to interact with core
4. **Plugin Registry** - Tracks installed plugins
5. **Plugin Loader** - Auto-loads active plugins

---

## 📁 Proposed Structure

```
s3vgroup/
├── app/
│   ├── Core/
│   │   ├── PluginManager.php        # Main plugin manager
│   │   ├── HookSystem.php           # Actions & Filters
│   │   ├── PluginRegistry.php       # Plugin registry
│   │   └── PluginLoader.php         # Plugin loader
│   │
│   ├── Plugins/                     # Core plugins (optional)
│   │   └── [plugin-name]/
│   │       ├── [PluginName].php
│   │       └── ...
│   │
│   └── [existing structure]
│
├── plugins/                         # User plugins directory
│   ├── my-plugin/
│   │   ├── my-plugin.php           # Main plugin file
│   │   ├── plugin.json             # Plugin metadata
│   │   └── [other files]
│   └── another-plugin/
│
└── [existing structure]
```

---

## 🔌 Plugin Structure

### Plugin File Example

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Plugin URI: https://example.com/my-plugin
 * Description: Adds awesome features to the site
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0
 * Text Domain: my-plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin initialization
add_action('plugins_loaded', function() {
    // Your plugin code here
});

// Add custom menu
add_action('admin_menu', function() {
    add_admin_menu_item([
        'title' => 'My Plugin',
        'slug' => 'my-plugin',
        'callback' => 'my_plugin_page',
        'icon' => '⚡',
        'position' => 50
    ]);
});

function my_plugin_page() {
    echo '<h1>My Plugin Page</h1>';
}
```

### Plugin Metadata (plugin.json)

```json
{
    "name": "My Awesome Plugin",
    "slug": "my-plugin",
    "version": "1.0.0",
    "description": "Adds awesome features",
    "author": "Your Name",
    "authorUri": "https://example.com",
    "pluginUri": "https://example.com/my-plugin",
    "requires": "1.0.0",
    "tested": "1.0.0",
    "license": "GPL-2.0",
    "textDomain": "my-plugin"
}
```

---

## 🎣 Hook System (Actions & Filters)

### Actions (Do Something)

```php
// Register an action
add_action('before_product_save', function($product) {
    // Do something before product is saved
    log_action('Product being saved: ' . $product['name']);
});

// Trigger an action
do_action('before_product_save', $product);
```

### Filters (Modify Something)

```php
// Register a filter
add_filter('product_price', function($price, $product) {
    // Modify the price
    return $price * 1.1; // Add 10% markup
}, 10, 2);

// Apply filters
$price = apply_filters('product_price', $originalPrice, $product);
```

---

## 🔧 Core Plugin API Functions

### Plugin Management

```php
// Activate plugin
activate_plugin('my-plugin');

// Deactivate plugin
deactivate_plugin('my-plugin');

// Check if plugin is active
is_plugin_active('my-plugin');

// Get plugin data
get_plugin_data('my-plugin');
```

### Hooks

```php
// Actions
add_action($hook, $callback, $priority = 10, $args = 1);
do_action($hook, ...$args);
remove_action($hook, $callback);

// Filters
add_filter($hook, $callback, $priority = 10, $args = 1);
apply_filters($hook, $value, ...$args);
remove_filter($hook, $callback);
```

### Admin Menu

```php
// Add admin menu item
add_admin_menu_item([
    'title' => 'My Page',
    'slug' => 'my-page',
    'callback' => 'my_page_function',
    'icon' => '⚡',
    'position' => 50,
    'parent' => null // or parent slug
]);
```

### Database

```php
// Get database connection
$db = get_db();

// Create custom table
create_plugin_table('my_table', $schema);

// Query helper
$results = query_plugin_data('SELECT * FROM my_table');
```

### Settings

```php
// Get plugin option
$value = get_plugin_option('my-plugin', 'my_option', 'default');

// Update plugin option
update_plugin_option('my-plugin', 'my_option', $value);

// Delete plugin option
delete_plugin_option('my-plugin', 'my_option');
```

---

## 📋 Implementation Plan

### Phase 1: Core Plugin System (Week 1)

1. **Create Plugin Manager**
   - Load plugins from `plugins/` directory
   - Parse plugin headers/metadata
   - Track active plugins in database
   - Handle activation/deactivation

2. **Create Hook System**
   - Action hooks (do_action, add_action)
   - Filter hooks (apply_filters, add_filter)
   - Priority system
   - Hook registry

3. **Create Plugin Loader**
   - Auto-discover plugins
   - Load active plugins on bootstrap
   - Handle plugin dependencies

### Phase 2: Plugin API (Week 2)

1. **Admin Integration**
   - Plugin management page
   - Install/activate/deactivate UI
   - Plugin settings pages

2. **Core Hooks**
   - Add hooks to existing system
   - Product hooks
   - Admin hooks
   - Frontend hooks

3. **Database Integration**
   - Plugin tables
   - Plugin options storage
   - Migration system for plugins

### Phase 3: Advanced Features (Week 3+)

1. **Plugin Dependencies**
   - Require other plugins
   - Version checking
   - Dependency resolution

2. **Plugin Updates**
   - Update mechanism
   - Version checking
   - Changelog system

3. **Plugin Marketplace** (Future)
   - Plugin repository
   - Install from URL
   - Rating/reviews

---

## 🎯 Core Hooks to Add

### Product Hooks

```php
// Before product save
do_action('before_product_save', $product);
do_action('after_product_save', $product, $id);

// Product display
apply_filters('product_name', $name, $product);
apply_filters('product_price', $price, $product);
apply_filters('product_description', $description, $product);
```

### Admin Hooks

```php
// Admin menu
do_action('admin_menu');
do_action('admin_init');

// Page load
do_action('admin_page_load', $page);
```

### Frontend Hooks

```php
// Before page render
do_action('before_page_render', $page);

// After page render
do_action('after_page_render', $page);

// Content filters
apply_filters('page_content', $content, $page);
```

---

## 📝 Example Plugins

### 1. SEO Plugin

```php
add_filter('page_meta_title', function($title, $page) {
    return $page['seo_title'] ?? $title;
}, 10, 2);

add_action('before_page_render', function($page) {
    // Add meta tags
    add_meta_tag('description', $page['seo_description']);
});
```

### 2. Analytics Plugin

```php
add_action('after_page_render', function($page) {
    // Track page view
    track_page_view($page['slug']);
});
```

### 3. Email Plugin

```php
add_action('quote_request_created', function($quote) {
    // Send email notification
    send_email('admin@example.com', 'New Quote Request', $quote);
});
```

---

## 🚀 Benefits

1. **Extensibility** - Add features without modifying core
2. **Modularity** - Features as separate plugins
3. **Community** - Others can develop plugins
4. **Maintainability** - Core stays clean
5. **Flexibility** - Enable/disable features easily

---

## 🔒 Security Considerations

1. **Plugin Validation** - Validate plugin code
2. **Permission System** - Control what plugins can do
3. **Sandboxing** - Isolate plugin execution
4. **Code Review** - Review plugin code before activation
5. **Update Security** - Secure update mechanism

---

## 📊 Database Schema

### plugins Table

```sql
CREATE TABLE plugins (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    version VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    path VARCHAR(500) NOT NULL,
    metadata JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
);
```

### plugin_options Table

```sql
CREATE TABLE plugin_options (
    id VARCHAR(255) PRIMARY KEY,
    plugin_slug VARCHAR(255) NOT NULL,
    option_key VARCHAR(255) NOT NULL,
    option_value TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_option (plugin_slug, option_key),
    INDEX idx_plugin (plugin_slug)
);
```

---

## ✅ Next Steps

1. **Create Core Classes** - PluginManager, HookSystem, etc.
2. **Add Hooks to Existing Code** - Integrate hooks into current system
3. **Create Plugin Directory** - Set up plugins folder
4. **Build Admin Interface** - Plugin management page
5. **Create Example Plugin** - Show how it works

---

**Status**: Ready to implement  
**Priority**: High - Foundation for extensibility  
**Estimated Time**: 2-3 weeks for core system

