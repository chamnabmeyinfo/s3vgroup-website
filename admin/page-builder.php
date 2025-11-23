<?php
/**
 * Page Builder - Alias to homepage-builder-v2.php with page_id parameter
 * This allows designing any page, not just homepage
 */

session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageId = $_GET['page_id'] ?? null;

if (!$pageId) {
    header('Location: /admin/pages.php');
    exit;
}

// Redirect to homepage-builder-v2.php with page_id parameter
header('Location: /admin/homepage-builder-v2.php?page_id=' . urlencode($pageId));
exit;

