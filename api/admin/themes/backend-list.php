<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Theme\ThemeRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;

AdminGuard::requireAuth();

$db = getDB();
$repository = new ThemeRepository($db);

// Get all active themes for backend selection
$themes = $repository->all(['is_active' => true]);

JsonResponse::success(['themes' => $themes]);

