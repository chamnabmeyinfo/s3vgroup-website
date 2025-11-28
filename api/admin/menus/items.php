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

$action = $_GET['action'] ?? '';

switch (\App\Http\Request::method()) {
    case 'POST':
        if ($action === 'create') {
            $controller->createItem();
        } elseif ($action === 'update-order') {
            $controller->updateOrder();
        } else {
            $controller->createItem(); // Default to create
        }
        break;

    case 'PUT':
    case 'PATCH':
        $controller->updateItem();
        break;

    case 'DELETE':
        $controller->deleteItem();
        break;

    default:
        \App\Http\JsonResponse::error('Method not allowed.', 405);
}

