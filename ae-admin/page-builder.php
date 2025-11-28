<?php
/**
 * Page Builder - Alias to homepage-builder-v2.php with page_id parameter
 * This allows designing any page, not just homepage
 */

session_start();
// Load bootstrap FIRST to ensure env() function is available
// Check ae-load.php first, then wp-load.php as fallback
if (file_exists(__DIR__ . '/../ae-load.php')) {
    require_once __DIR__ . '/../ae-load.php';
} else {
    require_once __DIR__ . '/../wp-load.php';
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
// Load functions (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/../ae-includes/functions.php')) {
    require_once __DIR__ . '/../ae-includes/functions.php';
} else {
    require_once __DIR__ . '/../wp-includes/functions.php';
}

requireAdmin();

$pageId = $_GET['page_id'] ?? null;

if (!$pageId) {
    header('Location: /admin/pages.php');
    exit;
}

// Redirect to homepage-builder-v2.php with page_id parameter
header('Location: /admin/homepage-builder-v2.php?page_id=' . urlencode($pageId));
exit;

