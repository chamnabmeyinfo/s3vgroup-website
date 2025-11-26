<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\ProductService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$db = getDB();
$repository = new ProductRepository($db);
$service = new ProductService($repository);

switch (Request::method()) {
    case 'GET':
        $filters = [
            'status'     => Request::query('status'),
            'categoryId' => Request::query('categoryId'),
            'search'     => Request::query('search'),
            'sortBy'     => Request::query('sortBy'),
            'sortOrder'  => Request::query('sortOrder'),
        ];

        // Support loading all products or paginated
        $loadAll = Request::query('all') === 'true' || Request::query('all') === '1';
        
        if ($loadAll) {
            // Load all products matching filters
            $products = $repository->all($filters);
            $totalCount = count($products);
            
            JsonResponse::success([
                'products' => $products,
                'total' => $totalCount,
                'count' => $totalCount,
            ]);
        } else {
            // Paginated results
            $limit = (int) Request::query('limit', 25);
            $offset = (int) Request::query('offset', 0);

            $products = $repository->paginateForAdmin($filters, $limit, $offset);
            
            // Get total count for pagination
            $allProducts = $repository->all($filters);
            $totalCount = count($allProducts);

            JsonResponse::success([
                'products' => $products,
                'pagination' => [
                    'limit'  => $limit,
                    'offset' => $offset,
                    'count'  => count($products),
                    'total'  => $totalCount,
                    'hasMore' => ($offset + $limit) < $totalCount,
                ],
            ]);
        }
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $product = $service->create($payload);
            JsonResponse::success(['product' => $product], 201);
        } catch (\InvalidArgumentException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

