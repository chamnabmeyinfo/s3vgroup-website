<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\CompanyStoryRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new CompanyStoryRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $story = $repository->find();
        JsonResponse::success(['story' => $story]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            // Check if story already exists
            $existing = $repository->find();
            if ($existing) {
                // Update existing
                $story = $repository->update($existing['id'], $payload);
            } else {
                // Create new
                $story = $repository->create($payload);
            }
            JsonResponse::success(['story' => $story]);
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;
        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $existing = $repository->find();
            if (!$existing) {
                JsonResponse::error('Company story not found. Please create it first.', 404);
            }

            $story = $repository->update($existing['id'], $payload);
            JsonResponse::success(['story' => $story]);
        } catch (\RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

