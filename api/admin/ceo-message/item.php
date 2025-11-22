<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\CeoMessageRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('CEO message id is required.', 422);
}

$repository = new CeoMessageRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $message = $repository->findById((string) $id);
        if (!$message) {
            JsonResponse::error('CEO message not found.', 404);
        }
        JsonResponse::success(['message' => $message]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;
        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }
        try {
            $message = $repository->update((string) $id, $payload);
            JsonResponse::success(['message' => $message]);
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

