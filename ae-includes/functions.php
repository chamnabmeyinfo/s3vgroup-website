<?php
/**
 * Helper Functions
 */

use App\Domain\Catalog\CategoryRepository;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Quotes\QuoteRequestRepository;
use App\Domain\Quotes\QuoteService;


/**
 * Configure and start session with proper HTTPS settings
 */
function startAdminSession() {
    // Configure session for HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Get featured categories
 */
function getFeaturedCategories($db, $limit = 6) {
    try {
        return (new CategoryRepository($db))->featured($limit);
    } catch (\PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Get featured products
 */
function getFeaturedProducts($db, $limit = 6) {
    try {
        return (new ProductRepository($db))->featured($limit);
    } catch (\PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all products with optional category filter
 */
function getAllProducts($db, $categorySlug = null, $limit = 50, $offset = 0) {
    try {
        return (new ProductRepository($db))->paginate($categorySlug, $limit, $offset);
    } catch (\PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product by slug (only published products)
 */
function getProductBySlug($db, $slug) {
    try {
        // Only return published products for public pages
        $product = (new ProductRepository($db))->findBySlug($slug, true);
        return $product;
    } catch (\PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all categories
 */
function getAllCategories($db) {
    try {
        return (new CategoryRepository($db))->all();
    } catch (\PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Submit quote request
 */
function submitQuote($db, $data) {
    try {
        $service = new QuoteService(new QuoteRequestRepository($db));
        $quote = $service->submit($data);
        return $quote['id'] ?? false;
    } catch (\Throwable $e) {
        error_log("Error submitting quote: " . $e->getMessage());
        return false;
    }
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Convert relative image URL to full URL
 * Ensures all image URLs have the full domain
 * Also fixes old WordPress paths to Ant Elite paths
 */
function fullImageUrl(?string $url): string {
    if (empty($url)) {
        return '';
    }

    // Handle comma-separated image lists (take the first one)
    if (str_contains($url, ',')) {
        $parts = explode(',', $url);
        $url = trim($parts[0]);
    }

    // Remove duplicated prefixes like "/ae-content/uploads/http://..."
    $url = preg_replace('#ae-content/uploads/(https?://)#i', '$1', $url, 1);
    
    // Detect if URL contains a nested full URL (malformed data - e.g., "/path/http://domain.com/image.jpg")
    // Extract the innermost URL if this is the case
    if (preg_match('/https?:\/\/[^\s<>"\']+/', $url, $matches)) {
        $url = $matches[0]; // Use the first full URL found
    }
    
    $isAbsolute = preg_match('/^https?:\/\//', $url) === 1;
    $containsLocalUploads = str_contains($url, '/uploads/')
        || str_contains($url, 'wp-content/uploads')
        || str_contains($url, 'ae-content/uploads');
    
    // For absolute URLs that point to remote assets (e.g., Unsplash), return as-is
    if ($isAbsolute && !$containsLocalUploads) {
        return $url;
    }
    
    // If the URL is absolute but references our uploads directory, normalize to the path portion
    if ($isAbsolute) {
        $parsed = parse_url($url);
        if ($parsed && isset($parsed['path'])) {
            $url = $parsed['path'];
        } else {
            // Fallback: try to extract path manually if parse_url fails
            if (preg_match('#(/uploads/[^?#]+|/ae-content/uploads/[^?#]+|/wp-content/uploads/[^?#]+)#', $url, $matches)) {
                $url = $matches[1];
            } else {
                // If we can't parse it, return as-is (might be malformed)
                return $url;
            }
        }
    }
    
    // Fix old WordPress paths to Ant Elite paths
    // 1. Fix wp-content -> ae-content
    $url = str_replace('wp-content/uploads/', 'ae-content/uploads/', $url);
    
    // 2. Fix naked uploads/ -> ae-content/uploads/ (if not already fixed)
    // This prevents double replacement if the path is already ae-content/uploads/
    if (!str_contains($url, 'ae-content/uploads/')) {
        $url = str_replace('uploads/', 'ae-content/uploads/', $url);
    }
    
    // Ensure path starts with /
    if (!str_starts_with($url, '/')) {
        $url = '/' . $url;
    }
    
    // Get site URL from config
    global $siteConfig;
    if (!isset($siteConfig)) {
        require_once __DIR__ . '/../config/site.php';
    }
    
    $siteUrl = $siteConfig['url'] ?? '';
    
    // Auto-detect if site URL not set
    if (empty($siteUrl)) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $siteUrl = $protocol . '://' . $host;
    }
    
    // Remove trailing slash from site URL
    $siteUrl = rtrim($siteUrl, '/');
    
    // Ensure URL starts with /
    $url = '/' . ltrim($url, '/');
    
    // Return full URL
    return $siteUrl . $url;
}

/**
 * Check if user is logged in (admin)
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}
