<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Catalog\CategoryRepository;
use App\Domain\Catalog\CategoryService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Category id is required.', 422);
}

$db = getDB();
$repository = new CategoryRepository($db);
$service = new CategoryService($repository);

switch (Request::method()) {
    case 'GET':
        $category = $repository->findById((string) $id);

        if (!$category) {
            JsonResponse::error('Category not found.', 404);
        }

        JsonResponse::success(['category' => $category]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $category = $service->update((string) $id, $payload);
            JsonResponse::success(['category' => $category]);
        } catch (\InvalidArgumentException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    case 'DELETE':
        try {
            $service->delete((string) $id);
            JsonResponse::success(['deleted' => true]);
        } catch (\RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 404);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

