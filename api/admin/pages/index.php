<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\PageRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new PageRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $pages = $repository->all();
        JsonResponse::success(['pages' => $pages]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            // Slug sanitization is handled in repository normalize() method
            // No need to manually generate slug here - repository handles it
            
            $page = $repository->create($payload);
            JsonResponse::success(['page' => $page], 201);
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

    default:
        JsonResponse::error('Method not allowed.', 405);
}

