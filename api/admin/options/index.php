<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Settings\SiteOptionRepository;
use App\Domain\Settings\SiteOptionService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new SiteOptionRepository(getDB());
$service = new SiteOptionService($repository);

switch (Request::method()) {
    case 'GET':
        $grouped = $service->getGrouped();
        JsonResponse::success(['options' => $grouped]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            if (isset($payload['bulk']) && is_array($payload['bulk'])) {
                // Bulk update
                $service->bulkUpdate($payload['bulk']);
                JsonResponse::success(['message' => 'Options updated successfully.']);
            } else {
                JsonResponse::error('Invalid request format.', 422);
            }
        } catch (\InvalidArgumentException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

