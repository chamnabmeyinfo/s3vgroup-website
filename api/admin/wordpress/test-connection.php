<?php
/**
 * WordPress Database Connection Test
 */

declare(strict_types=1);

// CRITICAL: Start output buffering IMMEDIATELY - before ANYTHING else
while (ob_get_level() > 0) {
    @ob_end_clean();
}
@ob_start();

// Suppress any errors/warnings that might output HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set timeout to prevent hanging
set_time_limit(30);
ini_set('max_execution_time', 30);

// Handle GET requests FIRST - before any database connection or session
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    http_response_code(405);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Method not allowed',
        'info' => 'This endpoint requires a POST request. Use the WordPress SQL Import page to test the connection.',
        'required_method' => 'POST',
        'usage' => 'This API endpoint is called from the admin panel, not directly from browser.',
        'endpoint_url' => '/api/admin/wordpress/test-connection.php',
        'admin_page' => '/admin/wordpress-sql-import.php'
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Load required files - same pattern as load-config.php and save-config.php
try {
    // Temporarily disable CacheControl to prevent header conflicts
    if (!defined('DISABLE_CACHE_CONTROL')) {
        define('DISABLE_CACHE_CONTROL', true);
    }
    
    require_once __DIR__ . '/../../../bootstrap/app.php';
    require_once __DIR__ . '/../../../config/database.php';
    
    // Clear any output from bootstrap
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    @ob_start();
} catch (Throwable $e) {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Configuration error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

use App\Http\JsonResponse;

// Check admin authentication - MUST be done before any output
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// Ensure we have a valid session before checking
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Clear any output before sending JSON error
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$host = $_POST['wp_host'] ?? '';
$database = $_POST['wp_database'] ?? '';
$username = $_POST['wp_username'] ?? '';
$password = $_POST['wp_password'] ?? '';
$prefix = $_POST['wp_prefix'] ?? 'wp_';

if (empty($host) || empty($database) || empty($username)) {
    JsonResponse::error('Missing required fields', 400);
}

try {
    // Connect to WordPress database
    // Handle port in host (e.g., "host:3306" or "host,3306")
    $hostParts = explode(':', $host);
    $dbHost = $hostParts[0];
    $dbPort = isset($hostParts[1]) ? (int)$hostParts[1] : 3306;
    
    // Build DSN with optional port
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$database};charset=utf8mb4";
    
    $wpDb = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10, // 10 second timeout
    ]);
    
    // Check if tables exist
    $postsTable = $prefix . 'posts';
    $termsTable = $prefix . 'terms';
    
    // Get WordPress version
    $wpVersion = 'Unknown';
    try {
        $versionStmt = $wpDb->query("SELECT option_value FROM {$prefix}options WHERE option_name = 'db_version' LIMIT 1");
        $dbVersion = $versionStmt->fetchColumn();
        if ($dbVersion) {
            $wpVersion = 'WordPress ' . $dbVersion;
        }
    } catch (Exception $e) {
        // Try alternative
        try {
            $versionStmt = $wpDb->query("SELECT option_value FROM {$prefix}options WHERE option_name = 'version' LIMIT 1");
            $wpVer = $versionStmt->fetchColumn();
            if ($wpVer) {
                $wpVersion = 'WordPress ' . $wpVer;
            }
        } catch (Exception $e2) {
            // Ignore
        }
    }
    
    // Check what post types exist
    $postTypes = [];
    try {
        $typesStmt = $wpDb->query("SELECT DISTINCT post_type FROM {$postsTable} WHERE post_type IS NOT NULL LIMIT 20");
        $postTypes = $typesStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // Ignore
    }
    
    // Count products (try different post types)
    $productsCount = 0;
    $productType = 'product';
    try {
        // First try 'product' (WooCommerce)
        $productsStmt = $wpDb->prepare("
            SELECT COUNT(*) 
            FROM {$postsTable} 
            WHERE post_type = 'product' 
            AND post_status IN ('publish', 'private')
        ");
        $productsStmt->execute();
        $productsCount = $productsStmt->fetchColumn();
        
        // If no products, try 'post' (regular WordPress posts)
        if ($productsCount == 0) {
            $productsStmt = $wpDb->prepare("
                SELECT COUNT(*) 
                FROM {$postsTable} 
                WHERE post_type = 'post' 
                AND post_status IN ('publish', 'private')
            ");
            $productsStmt->execute();
            $postCount = $productsStmt->fetchColumn();
            if ($postCount > 0) {
                $productsCount = $postCount;
                $productType = 'post';
            }
        }
    } catch (Exception $e) {
        // Table might not exist or different structure
    }
    
    // Count categories (try different taxonomies)
    $categoriesCount = 0;
    $categoryTaxonomy = 'product_cat';
    try {
        // First try 'product_cat' (WooCommerce)
        $categoriesStmt = $wpDb->prepare("
            SELECT COUNT(*) 
            FROM {$termsTable} t
            INNER JOIN {$prefix}term_taxonomy tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = 'product_cat'
        ");
        $categoriesStmt->execute();
        $categoriesCount = $categoriesStmt->fetchColumn();
        
        // If no product categories, try 'category' (regular WordPress categories)
        if ($categoriesCount == 0) {
            $categoriesStmt = $wpDb->prepare("
                SELECT COUNT(*) 
                FROM {$termsTable} t
                INNER JOIN {$prefix}term_taxonomy tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy = 'category'
            ");
            $categoriesStmt->execute();
            $catCount = $categoriesStmt->fetchColumn();
            if ($catCount > 0) {
                $categoriesCount = $catCount;
                $categoryTaxonomy = 'category';
            }
        }
    } catch (Exception $e) {
        // Table might not exist
    }
    
    // Get total posts count
    $totalPosts = 0;
    try {
        $totalStmt = $wpDb->query("SELECT COUNT(*) FROM {$postsTable}");
        $totalPosts = $totalStmt->fetchColumn();
    } catch (Exception $e) {
        // Ignore
    }
    
    JsonResponse::success([
        'message' => 'Connection successful',
        'wp_version' => $wpVersion,
        'stats' => [
            'products' => (int)$productsCount,
            'categories' => (int)$categoriesCount,
            'total_posts' => (int)$totalPosts,
            'product_type' => $productType,
            'category_taxonomy' => $categoryTaxonomy,
            'available_post_types' => $postTypes,
        ]
    ]);
    
} catch (PDOException $e) {
    $message = 'Connection failed';
    $suggestions = [];
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        $message = 'Access denied';
        $suggestions = [
            'Verify the username and password are correct',
            'Check if the database user has remote access permissions',
            'If connecting remotely, ensure the user is allowed from your server IP',
            'Try connecting from phpMyAdmin or MySQL client to verify credentials',
            'Check if the database user exists and has proper privileges'
        ];
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        $message = 'Database not found';
        $suggestions = [
            'Verify the database name is correct',
            'Check if the database exists in your WordPress hosting',
            'Ensure the database user has access to this database'
        ];
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'Connection timed out') !== false) {
        $message = 'Cannot connect to host';
        $suggestions = [
            'Verify the host address is correct (localhost, IP, or domain)',
            'Check if MySQL server is running on the remote host',
            'Verify firewall allows MySQL connections (port 3306)',
            'If remote, ensure MySQL allows remote connections',
            'Check if your hosting provider allows remote database connections'
        ];
    } else {
        $suggestions = [
            'Verify all connection details are correct',
            'Check if the database server is accessible',
            'Try connecting from phpMyAdmin to verify the connection works'
        ];
    }
    
    JsonResponse::error($message, 500, [
        'error' => $e->getMessage(),
        'suggestions' => $suggestions
    ]);
    exit;
} catch (Exception $e) {
    JsonResponse::error('Error: ' . $e->getMessage(), 500);
}

