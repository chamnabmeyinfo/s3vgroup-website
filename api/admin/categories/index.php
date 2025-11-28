<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Catalog\CategoryRepository;
use App\Domain\Catalog\CategoryService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$db = getDB();
$repository = new CategoryRepository($db);
$service = new CategoryService($repository);

switch (Request::method()) {
    case 'GET':
        $categories = $repository->all();
        
        // Add product count to each category
        $db = getDB();
        foreach ($categories as &$category) {
            $countStmt = $db->prepare('SELECT COUNT(*) as count FROM products WHERE categoryId = :id');
            $countStmt->execute([':id' => $category['id']]);
            $result = $countStmt->fetch(PDO::FETCH_ASSOC);
            $category['product_count'] = (int) ($result['count'] ?? 0);
        }
        unset($category); // Break reference
        
        JsonResponse::success([
            'categories' => $categories,
        ]);
        break;
    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $category = $service->create($payload);
            JsonResponse::success(['category' => $category], 201);
        } catch (\InvalidArgumentException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;
    default:
        JsonResponse::error('Method not allowed.', 405);
}

