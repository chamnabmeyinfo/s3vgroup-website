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

// If search query, use database search (much faster than PHP filtering)
if ($search && strlen($search) >= 2) {
    // Use repository's all() method with search filter - database does the filtering
    $filters = [
        'status' => 'PUBLISHED',
        'search' => $search
    ];
    if ($category) {
        $filters['categoryId'] = $category;
    }
    
    // Get all matching products (database handles search efficiently)
    $allMatchingProducts = $repository->all($filters);
    
    // Apply pagination in PHP (small array, fast)
    $products = array_slice($allMatchingProducts, $offset, $limit);
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

