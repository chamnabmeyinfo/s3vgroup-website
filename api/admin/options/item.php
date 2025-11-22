<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Settings\SiteOptionRepository;
use App\Domain\Settings\SiteOptionService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Option id is required.', 422);
}

$repository = new SiteOptionRepository(getDB());
$service = new SiteOptionService($repository);

switch (Request::method()) {
    case 'GET':
        $option = $repository->findById((string) $id);

        if (!$option) {
            JsonResponse::error('Option not found.', 404);
        }

        JsonResponse::success(['option' => $option]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $option = $service->update((string) $id, $payload);
            JsonResponse::success(['option' => $option]);
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

