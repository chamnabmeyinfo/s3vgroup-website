<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use App\Http\Controllers\MenuController;
use App\Application\Services\MenuService;
use App\Domain\Menus\MenuRepository;

$db = getDB();
$repository = new MenuRepository($db);
$service = new MenuService($repository);
$controller = new MenuController($service);

switch (\App\Http\Request::method()) {
    case 'GET':
        $controller->index();
        break;

    case 'POST':
        $controller->create();
        break;

    default:
        \App\Http\JsonResponse::error('Method not allowed.', 405);
}

