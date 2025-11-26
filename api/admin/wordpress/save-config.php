<?php
/**
 * Save WordPress Database Configuration
 */

ob_start();

require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

use App\Database\Connection;
use App\Domain\Settings\SiteOptionRepository;
use App\Http\JsonResponse;

// Check admin authentication
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ob_end_clean();
    JsonResponse::error('Unauthorized', 401);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    JsonResponse::error('Method not allowed', 405);
    exit;
}

try {
    ob_end_clean();
    
    $db = Connection::getInstance();
    $repository = new SiteOptionRepository($db);
    
    // Get configuration data
    $config = [
        'host' => $_POST['wp_host'] ?? '',
        'database' => $_POST['wp_database'] ?? '',
        'username' => $_POST['wp_username'] ?? '',
        'password' => $_POST['wp_password'] ?? '',
        'prefix' => $_POST['wp_prefix'] ?? 'wp_',
    ];
    
    // Encrypt password (simple base64 encoding - for production, use proper encryption)
    if (!empty($config['password'])) {
        $config['password'] = base64_encode($config['password']);
    }
    
    // Save configuration as JSON
    $configJson = json_encode($config);
    $repository->set('wordpress_db_config', $configJson, 'text');
    
    JsonResponse::success([
        'message' => 'Configuration saved successfully'
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    JsonResponse::error('Failed to save configuration: ' . $e->getMessage(), 500);
}

