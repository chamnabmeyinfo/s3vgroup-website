<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Domain\Catalog\CatalogService;
use App\Domain\Catalog\CategoryRepository;
use App\Domain\Catalog\ProductRepository;
use App\Http\JsonResponse;

$db = getDB();
$service = new CatalogService(
    new CategoryRepository($db),
    new ProductRepository($db)
);

JsonResponse::success($service->featured((int) ($_GET['limit'] ?? 6)));

