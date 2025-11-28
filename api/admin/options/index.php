<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use App\Http\Controllers\SiteOptionController;

$controller = new SiteOptionController();

switch (\App\Http\Request::method()) {
    case 'GET':
        $controller->index();
        break;

    case 'POST':
        $controller->bulkUpdate();
        break;

    default:
        \App\Http\JsonResponse::error('Method not allowed.', 405);
}

