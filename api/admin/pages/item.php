<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\PageRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new PageRepository(getDB());
$id = Request::query('id') ?? $_GET['id'] ?? null;

if (!$id) {
    JsonResponse::error('Page ID is required.', 400);
}

switch (Request::method()) {
    case 'GET':
        $page = $repository->findById($id);
        if (!$page) {
            JsonResponse::error('Page not found.', 404);
        }
        JsonResponse::success(['page' => $page]);
        break;

    case 'PUT':
    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            // Slug sanitization and uniqueness is handled in repository
            // No need to manually generate slug here
            
            $page = $repository->update($id, $payload);
            JsonResponse::success(['page' => $page]);
        } catch (\PDOException $exception) {
            // Check for duplicate slug error specifically
            if ($exception->getCode() === '23000' && strpos($exception->getMessage(), 'slug') !== false) {
                // This should not happen with ensureUniqueSlug, but handle it gracefully
                error_log('Duplicate slug error: ' . $exception->getMessage());
                JsonResponse::error('A page with this slug already exists. Please use a different slug.', 422);
            } else {
                error_log('Database error: ' . $exception->getMessage());
                JsonResponse::error('Database error occurred. Please try again.', 500);
            }
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    case 'DELETE':
        try {
            $repository->delete($id);
            JsonResponse::success(['deleted' => true]);
        } catch (\RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 404);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

