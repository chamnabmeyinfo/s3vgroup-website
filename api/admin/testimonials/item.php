<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\TestimonialRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Testimonial id is required.', 422);
}

$repository = new TestimonialRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $testimonial = $repository->findById((string) $id);
        if (!$testimonial) {
            JsonResponse::error('Testimonial not found.', 404);
        }
        JsonResponse::success(['testimonial' => $testimonial]);
        break;

    case 'PUT':
    case 'PATCH':
        $payload = Request::json() ?? $_POST;
        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }
        try {
            $testimonial = $repository->update((string) $id, $payload);
            JsonResponse::success(['testimonial' => $testimonial]);
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

