<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Domain\Catalog\CategoryRepository;
use App\Http\JsonResponse;
use App\Http\Request;

if (Request::method() !== 'GET') {
    JsonResponse::error('Method not allowed.', 405);
}

$db = getDB();
$repository = new CategoryRepository($db);
$limit = Request::query('limit');

if ($limit !== null) {
    JsonResponse::success([
        'categories' => $repository->featured((int) $limit),
    ]);
}

JsonResponse::success([
    'categories' => $repository->all(),
]);

