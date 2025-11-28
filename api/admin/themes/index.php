<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Theme\ThemeRepository;
use App\Domain\Theme\ThemeService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$db = getDB();
$repository = new ThemeRepository($db);
$service = new ThemeService($repository);

switch (Request::method()) {
    case 'GET':
        $filters = [
            'is_active' => Request::query('is_active') !== null
                ? filter_var(Request::query('is_active'), FILTER_VALIDATE_BOOLEAN)
                : null,
            'is_default' => Request::query('is_default') !== null
                ? filter_var(Request::query('is_default'), FILTER_VALIDATE_BOOLEAN)
                : null,
        ];

        $themes = $repository->all($filters);
        JsonResponse::success(['themes' => $themes]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $theme = $service->create($payload);
            JsonResponse::success(['theme' => $theme], 201);
        } catch (\App\Domain\Exceptions\ConflictException $e) {
            JsonResponse::error($e->getMessage(), 409);
        } catch (\App\Domain\Exceptions\ValidationException $e) {
            $details = $e->hasErrors() ? ['fields' => $e->getErrors()] : [];
            JsonResponse::error($e->getMessage(), 422, 'VALIDATION_ERROR', $details);
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

