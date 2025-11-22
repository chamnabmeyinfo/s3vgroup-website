<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Quotes\QuoteAdminService;
use App\Domain\Quotes\QuoteRequestRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Quote id is required.', 422);
}

$repository = new QuoteRequestRepository(getDB());
$service = new QuoteAdminService($repository);

switch (Request::method()) {
    case 'GET':
        $quote = $repository->findById((string) $id);

        if (!$quote) {
            JsonResponse::error('Quote not found.', 404);
        }

        JsonResponse::success(['quote' => $quote]);
        break;
    case 'PATCH':
    case 'PUT':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $quote = $service->update((string) $id, $payload);
            JsonResponse::success(['quote' => $quote]);
        } catch (\InvalidArgumentException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    case 'DELETE':
        try {
            $service->delete((string) $id);
            JsonResponse::success(['deleted' => true]);
        } catch (\RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

