<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Domain\Quotes\QuoteRequestRepository;
use App\Domain\Quotes\QuoteService;
use App\Http\JsonResponse;
use App\Http\Request;

if (Request::method() !== 'POST') {
    JsonResponse::error('Method not allowed.', 405);
}

$payload = Request::json() ?? $_POST;

if (!is_array($payload)) {
    JsonResponse::error('Invalid request body.', 400);
}

$service = new QuoteService(new QuoteRequestRepository(getDB()));

try {
    $quote = $service->submit($payload);
    JsonResponse::success(['quote' => $quote], 201);
} catch (\InvalidArgumentException $exception) {
    JsonResponse::error($exception->getMessage(), 422);
} catch (\Throwable $throwable) {
    error_log('Quote submission failed: ' . $throwable->getMessage());
    JsonResponse::error('Unable to submit quote at this time.', 500);
}

