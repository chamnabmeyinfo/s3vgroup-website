<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\SliderRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Slider id is required.', 422);
}

$repository = new SliderRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $slider = $repository->findById((string) $id);
        if (!$slider) {
            JsonResponse::error('Slider not found.', 404);
        }
        JsonResponse::success(['slider' => $slider]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;
        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }
        try {
            $slider = $repository->update((string) $id, $payload);
            JsonResponse::success(['slider' => $slider]);
        } catch (\RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    case 'DELETE':
        try {
            $repository->delete((string) $id);
            JsonResponse::success(['deleted' => true]);
        } catch (\RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 404);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

