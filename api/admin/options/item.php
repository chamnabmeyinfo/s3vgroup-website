<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use App\Domain\Exceptions\ValidationException;
use App\Http\Controllers\SiteOptionController;
use App\Http\JsonResponse;
use App\Http\Request;

$id = Request::query('id');

if (!$id) {
    JsonResponse::error('Option id is required.', 422, 'VALIDATION_ERROR', [
        'field' => 'id',
        'message' => 'The option id is required.',
    ]);
    exit;
}

$controller = new SiteOptionController();

switch (Request::method()) {
    case 'GET':
        $controller->show((string) $id);
        break;

    case 'PUT':
    case 'PATCH':
        $controller->update((string) $id);
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

