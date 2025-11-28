<?php
/**
 * Plugin Name: Example Plugin
 * Plugin URI: https://example.com/example-plugin
 * Description: A simple example plugin to demonstrate the plugin system
 * Version: 1.0.0
 * Author: S3V Group
 * Author URI: https://s3vgroup.com
 * License: GPL-2.0
 * Text Domain: example-plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin initialization
add_action('plugins_loaded', function() {
    // Plugin loaded successfully
    error_log('Example Plugin loaded!');
});

// Add a filter to modify product names
add_filter('product_name', function($name, $product) {
    // Add "NEW" badge to products created in last 7 days
    if (isset($product['createdAt'])) {
        $created = new DateTime($product['createdAt']);
        $now = new DateTime();
        $days = $now->diff($created)->days;
        
        if ($days <= 7) {
            return $name . ' <span class="badge-new">NEW</span>';
        }
    }
    
    return $name;
}, 10, 2);

// Add admin menu item
add_action('admin_menu', function() {
    add_admin_menu_item([
        'title' => 'Example Plugin',
        'slug' => 'example-plugin',
        'callback' => 'example_plugin_page',
        'icon' => '⚡',
        'position' => 100,
    ]);
});

function example_plugin_page() {
    ?>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Example Plugin</h1>
        <p class="text-gray-600 mb-4">This is an example plugin page!</p>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Plugin Information</h2>
            <ul class="space-y-2">
                <li><strong>Name:</strong> Example Plugin</li>
                <li><strong>Version:</strong> 1.0.0</li>
                <li><strong>Status:</strong> Active</li>
            </ul>
        </div>
        
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800">
                <strong>Note:</strong> This plugin demonstrates:
            </p>
            <ul class="list-disc list-inside mt-2 text-blue-700">
                <li>Plugin initialization</li>
                <li>Using filters to modify content</li>
                <li>Adding admin menu items</li>
            </ul>
        </div>
    </div>
    <?php
}

// Example: Log when a product is saved
add_action('before_product_save', function($product) {
    error_log('Example Plugin: Product being saved - ' . ($product['name'] ?? 'Unknown'));
});

