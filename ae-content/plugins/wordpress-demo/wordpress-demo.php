<?php
/**
 * Plugin Name: WordPress Ecosystem Demo
 * Plugin URI: https://example.com/wordpress-demo
 * Description: Demonstrates all WordPress-like features
 * Version: 1.0.0
 * Author: S3V Group
 * Author URI: https://s3vgroup.com
 * License: GPL-2.0
 * Text Domain: wordpress-demo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// OPTIONS API DEMO
// ============================================

add_action('plugins_loaded', function() {
    // Set default option if not exists
    if (get_option('wp_demo_option') === false) {
        add_option('wp_demo_option', 'Default Value');
    }
});

// ============================================
// SHORTCODES DEMO
// ============================================

add_shortcode('hello', function($atts, $content = '') {
    $name = $atts['name'] ?? 'World';
    $color = $atts['color'] ?? 'blue';
    
    return "<div style='color: {$color}; padding: 10px; border: 1px solid {$color};'>
        <strong>Hello, {$name}!</strong>
        " . ($content ? "<p>{$content}</p>" : "") . "
    </div>";
});

add_shortcode('current_date', function($atts) {
    $format = $atts['format'] ?? 'Y-m-d';
    return date($format);
});

// ============================================
// ASSET MANAGEMENT DEMO
// ============================================

add_action('wp_enqueue_scripts', function() {
    // Enqueue a demo script
    wp_enqueue_script(
        'wp-demo-script',
        plugins_url('assets/demo.js', __FILE__),
        [],
        '1.0.0',
        true
    );
    
    // Enqueue a demo style
    wp_enqueue_style(
        'wp-demo-style',
        plugins_url('assets/demo.css', __FILE__),
        [],
        '1.0.0'
    );
});

// ============================================
// ADMIN NOTICES DEMO
// ============================================

add_action('admin_notices', function() {
    add_admin_notice(
        'WordPress Demo Plugin is active! This is a demo notice.',
        'success',
        true
    );
});

// ============================================
// POST TYPE DEMO
// ============================================

add_action('init', function() {
    register_post_type('event', [
        'label' => 'Events',
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => '📅',
    ]);
});

// ============================================
// TAXONOMY DEMO
// ============================================

add_action('init', function() {
    register_taxonomy('event_category', 'event', [
        'label' => 'Event Categories',
        'hierarchical' => true, // Categories
        'show_admin_column' => true,
    ]);
    
    register_taxonomy('event_tag', 'event', [
        'label' => 'Event Tags',
        'hierarchical' => false, // Tags
        'show_admin_column' => true,
    ]);
});

// ============================================
// WIDGET DEMO
// ============================================

add_action('widgets_init', function() {
    register_widget('wp-demo-widget', 'Demo Widget', function($instance) {
        $title = $instance['title'] ?? 'Demo Widget';
        $content = $instance['content'] ?? 'This is a demo widget!';
        
        echo "<div class='wp-demo-widget'>";
        echo "<h3>{$title}</h3>";
        echo "<p>{$content}</p>";
        echo "</div>";
    }, [
        'description' => 'A demo widget showing WordPress-like functionality',
    ]);
    
    // Register sidebar
    register_sidebar([
        'name' => 'Demo Sidebar',
        'id' => 'demo-sidebar',
        'description' => 'A demo sidebar for widgets',
    ]);
});

// ============================================
// SETTINGS API DEMO
// ============================================

add_action('admin_init', function() {
    // Register setting
    register_setting('wp_demo_settings', 'wp_demo_text', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    
    // Add settings section
    add_settings_section(
        'wp_demo_main',
        'Main Settings',
        function() {
            echo '<p>Configure the WordPress Demo Plugin settings.</p>';
        },
        'wp_demo_settings'
    );
    
    // Add settings field
    add_settings_field(
        'wp_demo_text',
        'Demo Text Field',
        function($args) {
            $value = get_option('wp_demo_text', '');
            echo '<input type="text" name="wp_demo_text" value="' . esc_attr($value) . '" class="regular-text">';
            echo '<p class="description">Enter some demo text.</p>';
        },
        'wp_demo_settings',
        'wp_demo_main'
    );
});

// ============================================
// ADMIN MENU DEMO
// ============================================

add_action('admin_menu', function() {
    add_admin_menu_item([
        'title' => 'WP Demo',
        'slug' => 'wp-demo',
        'callback' => 'wp_demo_admin_page',
        'icon' => '🎯',
        'position' => 100,
    ]);
});

function wp_demo_admin_page() {
    ?>
    <div class="wrap">
        <h1>WordPress Ecosystem Demo</h1>
        
        <div class="card">
            <h2>Features Demonstrated</h2>
            <ul>
                <li>✅ Options API (get_option, update_option)</li>
                <li>✅ Asset Management (wp_enqueue_script, wp_enqueue_style)</li>
                <li>✅ Shortcodes ([hello], [current_date])</li>
                <li>✅ Post Types (Events)</li>
                <li>✅ Taxonomies (Event Categories, Event Tags)</li>
                <li>✅ Widgets (Demo Widget)</li>
                <li>✅ Settings API</li>
                <li>✅ Admin Notices</li>
            </ul>
        </div>
        
        <div class="card">
            <h2>Try Shortcodes</h2>
            <p>Use these shortcodes in your content:</p>
            <ul>
                <li><code>[hello name="John" color="red"]</code></li>
                <li><code>[current_date format="F j, Y"]</code></li>
            </ul>
        </div>
    </div>
    <?php
}

// ============================================
// HOOKS DEMO
// ============================================

// Filter product name
add_filter('product_name', function($name, $product) {
    // Add badge for new products
    if (isset($product['createdAt'])) {
        $created = new DateTime($product['createdAt']);
        $now = new DateTime();
        $days = $now->diff($created)->days;
        
        if ($days <= 7) {
            return $name . ' <span style="background: #4CAF50; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">NEW</span>';
        }
    }
    
    return $name;
}, 10, 2);

// Action when product is saved
add_action('after_product_save', function($product, $id) {
    error_log("WordPress Demo: Product saved - {$id}");
}, 10, 2);

