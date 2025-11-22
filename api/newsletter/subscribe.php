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
$name = trim($payload['name'] ?? '');
$source = $payload['source'] ?? 'website';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    JsonResponse::error('Valid email address is required.', 422);
}

if (!option('enable_newsletter', '1')) {
    JsonResponse::error('Newsletter subscription is currently disabled.', 503);
}

$repository = new NewsletterRepository(getDB());

try {
    $subscriber = $repository->subscribe($email, $name ?: null, $source ?: null);
    
    JsonResponse::success([
        'message' => 'Successfully subscribed to newsletter!',
        'subscriber' => [
            'email' => $subscriber['email'],
            'name' => $subscriber['name'],
        ],
    ]);
} catch (\Throwable $e) {
    error_log('Newsletter subscription error: ' . $e->getMessage());
    JsonResponse::error('Unable to subscribe. Please try again later.', 500);
}

