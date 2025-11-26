<?php
/**
 * Load WordPress Database Configuration
 */

ob_start();

require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../config/database.php';

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

try {
    ob_end_clean();
    
    $db = Connection::getInstance();
    $repository = new SiteOptionRepository($db);
    
    // Load configuration
    $configJson = $repository->get('wordpress_db_config');
    
    if ($configJson) {
        $config = json_decode($configJson, true);
        
        // Decrypt password
        if (!empty($config['password'])) {
            $config['password'] = base64_decode($config['password']);
        }
        
        // Don't send password in response for security (frontend will handle it)
        JsonResponse::success([
            'config' => $config
        ]);
    } else {
        JsonResponse::success([
            'config' => null
        ]);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    JsonResponse::error('Failed to load configuration: ' . $e->getMessage(), 500);
}

