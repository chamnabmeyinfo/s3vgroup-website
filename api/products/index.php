<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Domain\Catalog\ProductRepository;
use App\Http\JsonResponse;
use App\Http\Request;

if (Request::method() !== 'GET') {
    JsonResponse::error('Method not allowed.', 405);
}

$category = Request::query('category');
$search = Request::query('search');
$limit = (int) (Request::query('limit', 20));
$offset = (int) (Request::query('offset', 0));

$repository = new ProductRepository(getDB());

// If search query, filter products (only published)
if ($search && strlen($search) >= 2) {
    // Use paginate with search filter - but we need to filter by published status
    // For now, get all published products and filter in PHP (for search)
    // Note: This could be optimized with a search method in repository
    $allProducts = $repository->all(['status' => 'PUBLISHED']);
    $searchLower = strtolower($search);
    $products = array_filter($allProducts, function($product) use ($searchLower, $category) {
        // Only include published products
        if (($product['status'] ?? '') !== 'PUBLISHED') {
            return false;
        }
        
        $match = str_contains(strtolower($product['name']), $searchLower) ||
                 str_contains(strtolower($product['summary'] ?? ''), $searchLower) ||
                 str_contains(strtolower($product['description'] ?? ''), $searchLower);
        
        if ($category && $product['categoryId'] !== $category) {
            return false;
        }
        
        return $match;
    });
    
    $products = array_slice(array_values($products), $offset, $limit);
} else {
    // paginate() already filters by PUBLISHED status
    $products = $repository->paginate($category ? (string) $category : null, $limit, $offset);
}

JsonResponse::success([
    'products' => $products,
    'pagination' => [
        'limit'  => $limit,
        'offset' => $offset,
        'count'  => count($products),
    ],
]);

