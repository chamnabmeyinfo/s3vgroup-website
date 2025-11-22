<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\SliderRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new SliderRepository(getDB());

switch (Request::method()) {
    case 'GET':
        $sliders = $repository->all();
        JsonResponse::success(['sliders' => $sliders]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            $slider = $repository->create($payload);
            JsonResponse::success(['slider' => $slider]);
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

