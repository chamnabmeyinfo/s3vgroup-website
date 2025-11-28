<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Theme\ThemeRepository;
use App\Domain\Theme\ThemeService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$identifier = Request::query('id') ?? Request::query('slug') ?? (Request::json()['id'] ?? Request::json()['slug'] ?? null);

if (!$identifier) {
    JsonResponse::error('Theme id or slug is required.', 422);
}

$db = getDB();
$repository = new ThemeRepository($db);
$service = new ThemeService($repository);

$theme = $repository->findById((string) $identifier)
    ?? $repository->findBySlug((string) $identifier);

if (!$theme) {
    JsonResponse::error('Theme not found.', 404);
}

try {
    $service->setAsDefault($theme['id']);
    $updated = $repository->findById($theme['id']);
    JsonResponse::success(['theme' => $updated]);
} catch (\App\Domain\Exceptions\ConflictException $e) {
    JsonResponse::error($e->getMessage(), 409);
} catch (\InvalidArgumentException $e) {
    JsonResponse::error($e->getMessage(), 400);
}

