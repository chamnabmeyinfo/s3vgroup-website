<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Http\AdminGuard;
use App\Http\JsonResponse;
use PDO;

try {
    AdminGuard::requireAuth();
} catch (\Throwable $e) {
    JsonResponse::error('Authentication required.', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    JsonResponse::error('Method not allowed.', 405);
}

try {
    $db = getDB();
    
    // Total products
    $totalStmt = $db->query("SELECT COUNT(*) as total FROM products");
    $total = (int) $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Products by status
    $statusStmt = $db->query("
        SELECT status, COUNT(*) as count 
        FROM products 
        GROUP BY status
    ");
    $statusCounts = [];
    while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
        $statusCounts[$row['status']] = (int) $row['count'];
    }
    
    // Products by category
    $categoryStmt = $db->query("
        SELECT 
            c.id,
            c.name,
            COUNT(p.id) as count
        FROM categories c
        LEFT JOIN products p ON c.id = p.categoryId
        GROUP BY c.id, c.name
        ORDER BY count DESC, c.name ASC
    ");
    $categoryStats = [];
    while ($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
        $categoryStats[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'count' => (int) $row['count'],
        ];
    }
    
    // Products with/without images
    $imageStmt = $db->query("
        SELECT 
            CASE 
                WHEN heroImage IS NOT NULL AND heroImage != '' THEN 'with_image'
                ELSE 'without_image'
            END as type,
            COUNT(*) as count
        FROM products
        GROUP BY type
    ");
    $imageStats = ['with_image' => 0, 'without_image' => 0];
    while ($row = $imageStmt->fetch(PDO::FETCH_ASSOC)) {
        $imageStats[$row['type']] = (int) $row['count'];
    }
    
    // Products with/without prices
    $priceStmt = $db->query("
        SELECT 
            CASE 
                WHEN price IS NOT NULL AND price > 0 THEN 'with_price'
                ELSE 'without_price'
            END as type,
            COUNT(*) as count
        FROM products
        GROUP BY type
    ");
    $priceStats = ['with_price' => 0, 'without_price' => 0];
    while ($row = $priceStmt->fetch(PDO::FETCH_ASSOC)) {
        $priceStats[$row['type']] = (int) $row['count'];
    }
    
    // Products with/without SKU
    $skuStmt = $db->query("
        SELECT 
            CASE 
                WHEN sku IS NOT NULL AND sku != '' THEN 'with_sku'
                ELSE 'without_sku'
            END as type,
            COUNT(*) as count
        FROM products
        GROUP BY type
    ");
    $skuStats = ['with_sku' => 0, 'without_sku' => 0];
    while ($row = $skuStmt->fetch(PDO::FETCH_ASSOC)) {
        $skuStats[$row['type']] = (int) $row['count'];
    }
    
    // Price statistics
    $priceStatsDetail = $db->query("
        SELECT 
            COUNT(*) as total_with_price,
            MIN(price) as min_price,
            MAX(price) as max_price,
            AVG(price) as avg_price,
            SUM(price) as total_value
        FROM products
        WHERE price IS NOT NULL AND price > 0
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Recent products (last 7 days)
    $recentStmt = $db->query("
        SELECT COUNT(*) as count
        FROM products
        WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $recentCount = (int) $recentStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Updated products (last 7 days)
    $updatedStmt = $db->query("
        SELECT COUNT(*) as count
        FROM products
        WHERE updatedAt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $updatedCount = (int) $updatedStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Products created by month (last 6 months)
    $monthlyStmt = $db->query("
        SELECT 
            DATE_FORMAT(createdAt, '%Y-%m') as month,
            COUNT(*) as count
        FROM products
        WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(createdAt, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthlyStats = [];
    while ($row = $monthlyStmt->fetch(PDO::FETCH_ASSOC)) {
        $monthlyStats[] = [
            'month' => $row['month'],
            'count' => (int) $row['count'],
        ];
    }
    
    // Top categories by product count
    $topCategories = array_slice($categoryStats, 0, 10);
    
    JsonResponse::success([
        'total' => $total,
        'byStatus' => [
            'PUBLISHED' => $statusCounts['PUBLISHED'] ?? 0,
            'DRAFT' => $statusCounts['DRAFT'] ?? 0,
            'ARCHIVED' => $statusCounts['ARCHIVED'] ?? 0,
        ],
        'byCategory' => $categoryStats,
        'topCategories' => $topCategories,
        'images' => $imageStats,
        'prices' => $priceStats,
        'skus' => $skuStats,
        'priceDetails' => [
            'totalWithPrice' => (int) ($priceStatsDetail['total_with_price'] ?? 0),
            'minPrice' => $priceStatsDetail['min_price'] ? (float) $priceStatsDetail['min_price'] : null,
            'maxPrice' => $priceStatsDetail['max_price'] ? (float) $priceStatsDetail['max_price'] : null,
            'avgPrice' => $priceStatsDetail['avg_price'] ? (float) $priceStatsDetail['avg_price'] : null,
            'totalValue' => $priceStatsDetail['total_value'] ? (float) $priceStatsDetail['total_value'] : null,
        ],
        'recent' => [
            'last7Days' => $recentCount,
            'updatedLast7Days' => $updatedCount,
        ],
        'monthly' => $monthlyStats,
    ]);
    
} catch (\Throwable $e) {
    error_log('Statistics error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    JsonResponse::error('Failed to load statistics: ' . $e->getMessage(), 500);
}

