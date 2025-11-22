<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Quotes\QuoteAdminService;
use App\Domain\Quotes\QuoteRequestRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

if (Request::method() !== 'GET') {
    JsonResponse::error('Method not allowed.', 405);
}

$filters = [
    'status' => Request::query('status'),
    'email'  => Request::query('email'),
];

$limit = (int) Request::query('limit', 25);
$offset = (int) Request::query('offset', 0);

$service = new QuoteAdminService(new QuoteRequestRepository(getDB()));
$quotes = $service->list($filters, $limit, $offset);

JsonResponse::success([
    'quotes' => $quotes,
    'pagination' => [
        'limit'  => $limit,
        'offset' => $offset,
        'count'  => count($quotes),
    ],
]);

