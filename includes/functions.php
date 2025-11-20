<?php
/**
 * Helper Functions
 */

/**
 * Get featured categories
 */
function getFeaturedCategories($db, $limit = 6) {
    try {
        $stmt = $db->prepare("SELECT * FROM categories ORDER BY priority DESC, name ASC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Get featured products
 */
function getFeaturedProducts($db, $limit = 6) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.categoryId = c.id 
            WHERE p.status = 'PUBLISHED' 
            ORDER BY p.updatedAt DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all products with optional category filter
 */
function getAllProducts($db, $categorySlug = null, $limit = 50, $offset = 0) {
    try {
        if ($categorySlug) {
            $stmt = $db->prepare("
                SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                LEFT JOIN categories c ON p.categoryId = c.id 
                WHERE p.status = 'PUBLISHED' AND c.slug = ?
                ORDER BY p.updatedAt DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$categorySlug, $limit, $offset]);
        } else {
            $stmt = $db->prepare("
                SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                LEFT JOIN categories c ON p.categoryId = c.id 
                WHERE p.status = 'PUBLISHED' 
                ORDER BY p.updatedAt DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product by slug
 */
function getProductBySlug($db, $slug) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.categoryId = c.id 
            WHERE p.slug = ? AND p.status = 'PUBLISHED'
        ");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Parse JSON fields
            if ($product['specs']) {
                $product['specs'] = json_decode($product['specs'], true);
            }
            if ($product['highlights']) {
                $product['highlights'] = json_decode($product['highlights'], true) ?: [];
            }
        }
        
        return $product;
    } catch (PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all categories
 */
function getAllCategories($db) {
    try {
        $stmt = $db->query("SELECT * FROM categories ORDER BY priority DESC, name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Submit quote request
 */
function submitQuote($db, $data) {
    try {
        $stmt = $db->prepare("
            INSERT INTO quote_requests (id, companyName, contactName, email, phone, message, items, status, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'NEW', NOW(), NOW())
        ");
        
        $id = uniqid('quote_', true);
        $items = isset($data['items']) ? json_encode($data['items']) : null;
        
        $stmt->execute([
            $id,
            $data['companyName'],
            $data['contactName'],
            $data['email'],
            $data['phone'] ?? null,
            $data['message'] ?? null,
            $items
        ]);
        
        return $id;
    } catch (PDOException $e) {
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
