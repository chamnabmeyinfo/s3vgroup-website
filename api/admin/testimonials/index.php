<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\TestimonialRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new TestimonialRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $testimonials = $repository->all();
        JsonResponse::success(['testimonials' => $testimonials]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $testimonial = $repository->create($payload);
            JsonResponse::success(['testimonial' => $testimonial]);
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

