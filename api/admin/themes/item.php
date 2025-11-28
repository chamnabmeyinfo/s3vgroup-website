<?php

declare(strict_types=1);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log
ini_set('log_errors', 1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Theme\ThemeRepository;
use App\Domain\Theme\ThemeService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$identifier = Request::query('id') ?? Request::query('slug');

if (!$identifier) {
    JsonResponse::error('Theme id or slug is required.', 422);
}

$db = getDB();
$repository = new ThemeRepository($db);
$service = new ThemeService($repository);

switch (Request::method()) {
    case 'GET':
        $theme = $repository->findById((string) $identifier)
            ?? $repository->findBySlug((string) $identifier);

        if (!$theme) {
            JsonResponse::error('Theme not found.', 404);
        }

        JsonResponse::success(['theme' => $theme]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        $theme = $repository->findById((string) $identifier)
            ?? $repository->findBySlug((string) $identifier);

        if (!$theme) {
            JsonResponse::error('Theme not found.', 404);
        }

        try {
            $updated = $service->update($theme['id'], $payload);
            
            // Clear theme cache after update
            require_once __DIR__ . '/../../../ae-admin/includes/theme-loader.php';
            ThemeLoader::clearCache();
            
            JsonResponse::success(['theme' => $updated]);
        } catch (\App\Domain\Exceptions\ConflictException $e) {
            JsonResponse::error($e->getMessage(), 409);
        } catch (\App\Domain\Exceptions\ValidationException $e) {
            $details = $e->hasErrors() ? ['fields' => $e->getErrors()] : [];
            JsonResponse::error($e->getMessage(), 422, 'VALIDATION_ERROR', $details);
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            error_log('Theme update error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            JsonResponse::error('An error occurred while updating the theme: ' . $e->getMessage(), 500);
        }
        break;

    case 'DELETE':
        $theme = $repository->findById((string) $identifier)
            ?? $repository->findBySlug((string) $identifier);

        if (!$theme) {
            JsonResponse::error('Theme not found.', 404);
        }

        try {
            $service->delete($theme['id']);
            JsonResponse::success(['message' => 'Theme deleted successfully.']);
        } catch (\App\Domain\Exceptions\ConflictException $e) {
            JsonResponse::error($e->getMessage(), 409);
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

