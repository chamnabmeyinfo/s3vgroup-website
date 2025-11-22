<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\ProductService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Product id is required.', 422);
}

$db = getDB();
$repository = new ProductRepository($db);
$service = new ProductService($repository);

switch (Request::method()) {
    case 'GET':
        $product = $repository->findById((string) $id);

        if (!$product) {
            JsonResponse::error('Product not found.', 404);
        }

        JsonResponse::success(['product' => $product]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $product = $service->update((string) $id, $payload);
            JsonResponse::success(['product' => $product]);
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

