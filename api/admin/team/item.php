<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\TeamMemberRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Team member id is required.', 422);
}

$repository = new TeamMemberRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $member = $repository->findById((string) $id);
        if (!$member) {
            JsonResponse::error('Team member not found.', 404);
        }
        JsonResponse::success(['member' => $member]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;
        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }
        try {
            $member = $repository->update((string) $id, $payload);
            JsonResponse::success(['member' => $member]);
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

