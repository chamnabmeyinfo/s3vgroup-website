<?php
/**
 * Helper Functions
 */

use App\Domain\Catalog\CategoryRepository;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Quotes\QuoteRequestRepository;
use App\Domain\Quotes\QuoteService;

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
 * Get product by slug
 */
function getProductBySlug($db, $slug) {
    try {
        $product = (new ProductRepository($db))->findBySlug($slug);
        return $product && ($product['status'] ?? 'DRAFT') === 'PUBLISHED' ? $product : null;
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
