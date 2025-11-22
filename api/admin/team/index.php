<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\TeamMemberRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new TeamMemberRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $members = $repository->all();
        JsonResponse::success(['team' => $members]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $member = $repository->create($payload);
            JsonResponse::success(['member' => $member]);
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

