<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

use App\Domain\Content\NewsletterRepository;
use App\Http\JsonResponse;
use App\Http\Request;

if (Request::method() !== 'POST') {
    JsonResponse::error('Method not allowed.', 405);
}

$payload = Request::json() ?? $_POST;
$email = trim($payload['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    JsonResponse::error('Valid email address is required.', 422);
}

$repository = new NewsletterRepository(getDB());

try {
    $repository->unsubscribe($email);
    
    JsonResponse::success([
        'message' => 'Successfully unsubscribed from newsletter.',
    ]);
} catch (\Throwable $e) {
    error_log('Newsletter unsubscribe error: ' . $e->getMessage());
    JsonResponse::error('Unable to unsubscribe. Please try again later.', 500);
}

