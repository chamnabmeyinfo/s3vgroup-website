<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Domain\Catalog\ProductRepository;
use App\Http\JsonResponse;
use App\Http\Request;

if (Request::method() !== 'GET') {
    JsonResponse::error('Method not allowed.', 405);
}

$slug = Request::query('slug');

if (!$slug) {
    JsonResponse::error('Product slug is required.', 422);
}

$repository = new ProductRepository(getDB());
$product = $repository->findBySlug((string) $slug);

if (!$product) {
    JsonResponse::error('Product not found.', 404);
}

JsonResponse::success(['product' => $product]);

