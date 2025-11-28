# 🔌 Plugin Development Guide

## Quick Start

### 1. Create Plugin Directory

Create a folder in the `plugins/` directory:

```bash
plugins/my-awesome-plugin/
```

### 2. Create Main Plugin File

Create `my-awesome-plugin.php` in your plugin directory:

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

// Your plugin code here
add_action('plugins_loaded', function() {
    // Plugin initialized
});
```

### 3. Discover Plugin

1. Go to **Admin → Plugins**
2. Click **"Discover Plugins"**
3. Your plugin will appear in the list
4. Click **"Activate"** to enable it

---

## Available Hooks

### Actions (Do Something)

```php
// When plugins are loaded
add_action('plugins_loaded', function() {
    // Your code
});

// Before product is saved
add_action('before_product_save', function($product) {
    // Log, validate, etc.
});

// After product is saved
add_action('after_product_save', function($product, $id) {
    // Send notification, update cache, etc.
});

// Admin menu initialization
add_action('admin_menu', function() {
    // Add menu items
});
```

### Filters (Modify Something)

```php
// Modify product name
add_filter('product_name', function($name, $product) {
    return strtoupper($name); // Example: Make uppercase
}, 10, 2);

// Modify product price
add_filter('product_price', function($price, $product) {
    return $price * 1.1; // Add 10% markup
}, 10, 2);

// Modify page content
add_filter('page_content', function($content, $page) {
    return $content . '<p>Custom footer</p>';
}, 10, 2);
```

---

## Plugin API Functions

### Database

```php
// Get database connection
$db = get_db();

// Query database
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
```

### Options

```php
// Get plugin option
$value = get_plugin_option('my-plugin', 'my_option', 'default');

// Update plugin option
update_plugin_option('my-plugin', 'my_option', 'new value');

// Delete plugin option
delete_plugin_option('my-plugin', 'my_option');
```

### Admin Menu

```php
add_action('admin_menu', function() {
    add_admin_menu_item([
        'title' => 'My Plugin',
        'slug' => 'my-plugin',
        'callback' => 'my_plugin_page',
        'icon' => '⚡',
        'position' => 50,
    ]);
});

function my_plugin_page() {
    echo '<h1>My Plugin Page</h1>';
}
```

---

## Example Plugins

### 1. SEO Plugin

```php
<?php
/**
 * Plugin Name: SEO Enhancer
 * Version: 1.0.0
 */

add_filter('page_meta_title', function($title, $page) {
    return $page['seo_title'] ?? $title;
}, 10, 2);

add_action('before_page_render', function($page) {
    // Add meta tags
    echo '<meta name="description" content="' . htmlspecialchars($page['seo_description'] ?? '') . '">';
});
```

### 2. Analytics Plugin

```php
<?php
/**
 * Plugin Name: Analytics Tracker
 * Version: 1.0.0
 */

add_action('after_page_render', function($page) {
    // Track page view
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO page_views (page_slug, viewed_at) VALUES (?, NOW())");
    $stmt->execute([$page['slug']]);
});
```

### 3. Email Notification Plugin

```php
<?php
/**
 * Plugin Name: Email Notifications
 * Version: 1.0.0
 */

add_action('quote_request_created', function($quote) {
    // Send email
    $to = get_plugin_option('email-notifications', 'admin_email', 'admin@example.com');
    $subject = 'New Quote Request';
    $message = "New quote request from: {$quote['name']}";
    mail($to, $subject, $message);
});
```

---

## Best Practices

1. **Use Hooks** - Don't modify core files, use hooks instead
2. **Namespace** - Use unique function names or namespaces
3. **Error Handling** - Always handle errors gracefully
4. **Security** - Validate and sanitize all inputs
5. **Documentation** - Comment your code well

---

## Plugin Structure

```
my-plugin/
├── my-plugin.php          # Main plugin file (required)
├── includes/              # Optional: Additional PHP files
│   └── functions.php
├── assets/                # Optional: CSS, JS, images
│   ├── css/
│   └── js/
└── README.md             # Optional: Documentation
```

---

## Testing Your Plugin

1. **Activate** - Activate plugin in admin
2. **Check Logs** - Check error logs for issues
3. **Test Hooks** - Verify hooks are firing
4. **Test Filters** - Verify filters are modifying data
5. **Test Admin** - Verify admin pages work

---

## Common Issues

### Plugin Not Loading

- Check plugin file exists
- Verify plugin headers are correct
- Check error logs

### Hooks Not Firing

- Verify hook name is correct
- Check plugin is activated
- Verify hook is called in core code

### Filters Not Working

- Check filter priority
- Verify filter is applied in core code
- Check return value

---

## Next Steps

1. Read `PLUGIN-SYSTEM-ARCHITECTURE.md` for full documentation
2. Check `plugins/example-plugin/` for working example
3. Start building your plugin!

---

**Happy Plugin Development!** 🚀

