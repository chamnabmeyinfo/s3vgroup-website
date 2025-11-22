<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\HomepageSectionRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new HomepageSectionRepository(getDB());

// Get ID from query parameter (most common) or URL path
$id = Request::query('id') ?? $_GET['id'] ?? null;

// Try to get from URL path if not in query
if (!$id) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($uri, PHP_URL_PATH);
    if ($path) {
        $parts = array_filter(explode('/', trim($path, '/')));
        $parts = array_values($parts);
        // Find 'item.php' in the path and get the next segment
        $itemIndex = array_search('item.php', $parts);
        if ($itemIndex !== false && isset($parts[$itemIndex + 1])) {
            $id = $parts[$itemIndex + 1];
        }
    }
}

if (!$id) {
    JsonResponse::error('Section ID is required.', 400);
}

switch (Request::method()) {
    case 'GET':
        $section = $repository->findById($id);
        if (!$section) {
            JsonResponse::error('Section not found.', 404);
        }
        JsonResponse::success(['section' => $section]);
        break;

    case 'PUT':
    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $section = $repository->update($id, $payload);
            JsonResponse::success(['section' => $section]);
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

