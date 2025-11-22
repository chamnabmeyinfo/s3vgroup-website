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
        JsonResponse::success([
            'categories' => $repository->all(),
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

