<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\MenuService;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Exceptions\ValidationException;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    /**
     * Get all menus
     */
    public function index(): void
    {
        try {
            $menus = $this->menuService->getAllMenus();
            JsonResponse::success(['menus' => $menus]);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Get menu by ID with items
     */
    public function show(): void
    {
        try {
            $id = Request::query('id');
            if (!$id) {
                JsonResponse::error('Menu ID is required', 400);
                return;
            }

            $menu = $this->menuService->getMenuWithItems($id);
            JsonResponse::success(['menu' => $menu]);
        } catch (NotFoundException $e) {
            JsonResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Get menu by location
     */
    public function getByLocation(): void
    {
        try {
            $location = Request::query('location', 'primary');
            $menu = $this->menuService->getMenuByLocation($location);
            
            if ($menu) {
                $menu['items'] = $this->menuService->getMenuWithItems($menu['id'])['items'] ?? [];
            }
            
            JsonResponse::success(['menu' => $menu]);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Create menu
     */
    public function create(): void
    {
        try {
            $data = Request::json();
            
            if (empty($data['name'])) {
                JsonResponse::error('Menu name is required', 400);
                return;
            }

            $id = $this->menuService->createMenu($data);
            $menu = $this->menuService->getMenuById($id);
            
            JsonResponse::success(['menu' => $menu], 201);
        } catch (ValidationException $e) {
            JsonResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Update menu
     */
    public function update(): void
    {
        try {
            $id = Request::query('id');
            if (!$id) {
                JsonResponse::error('Menu ID is required', 400);
                return;
            }

            $data = Request::json();
            $this->menuService->updateMenu($id, $data);
            
            $menu = $this->menuService->getMenuById($id);
            JsonResponse::success(['menu' => $menu]);
        } catch (NotFoundException $e) {
            JsonResponse::error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            JsonResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete menu
     */
    public function delete(): void
    {
        try {
            $id = Request::query('id');
            if (!$id) {
                JsonResponse::error('Menu ID is required', 400);
                return;
            }

            $this->menuService->deleteMenu($id);
            JsonResponse::success(['message' => 'Menu deleted successfully']);
        } catch (NotFoundException $e) {
            JsonResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Create menu item
     */
    public function createItem(): void
    {
        try {
            $data = Request::json();
            
            if (empty($data['menu_id'])) {
                JsonResponse::error('Menu ID is required', 400);
                return;
            }

            $id = $this->menuService->createMenuItem($data);
            JsonResponse::success(['id' => $id], 201);
        } catch (ValidationException $e) {
            JsonResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Update menu item
     */
    public function updateItem(): void
    {
        try {
            $id = Request::query('id');
            if (!$id) {
                JsonResponse::error('Menu item ID is required', 400);
                return;
            }

            $data = Request::json();
            $this->menuService->updateMenuItem($id, $data);
            
            JsonResponse::success(['message' => 'Menu item updated successfully']);
        } catch (ValidationException $e) {
            JsonResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete menu item
     */
    public function deleteItem(): void
    {
        try {
            $id = Request::query('id');
            if (!$id) {
                JsonResponse::error('Menu item ID is required', 400);
                return;
            }

            $this->menuService->deleteMenuItem($id);
            JsonResponse::success(['message' => 'Menu item deleted successfully']);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Update menu items order
     */
    public function updateOrder(): void
    {
        try {
            $data = Request::json();
            
            if (empty($data['items']) || !is_array($data['items'])) {
                JsonResponse::error('Items array is required', 400);
                return;
            }

            $this->menuService->updateMenuItemsOrder($data['items']);
            JsonResponse::success(['message' => 'Menu order updated successfully']);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }
}

