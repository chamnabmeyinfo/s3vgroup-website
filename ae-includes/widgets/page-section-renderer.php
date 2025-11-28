<?php
/**
 * Page Section Renderer
 * Renders sections for custom pages (reuses homepage section renderer logic)
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../bootstrap/app.php';
}

use App\Database\Connection;

// Get section data
$content = $section['content'] ?? [];
$settings = $section['settings'] ?? [];
$title = $section['title'] ?? '';

// Get colors if not already set
if (!isset($primaryColor)) {
    $primaryColor = option('primary_color', '#0b3a63');
    $secondaryColor = option('secondary_color', '#1a5a8a');
    $accentColor = option('accent_color', '#fa4f26');
}

// Get database connection if not set
if (!isset($db)) {
    $db = Connection::getInstance();
}

// Reuse the renderSection function from homepage-section-renderer
if (!function_exists('renderSection')) {
    require_once __DIR__ . '/homepage-section-renderer.php';
}

// Render the section
renderSection($section, $primaryColor, $secondaryColor, $accentColor, $db);

