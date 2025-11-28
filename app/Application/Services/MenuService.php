<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Menus\MenuRepository;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Exceptions\ValidationException;

class MenuService
{
    public function __construct(
        private MenuRepository $repository
    ) {}

    /**
     * Get all menus
     */
    public function getAllMenus(): array
    {
        return $this->repository->all();
    }

    /**
     * Get menu by ID
     */
    public function getMenuById(string $id): array
    {
        $menu = $this->repository->findById($id);
        if (!$menu) {
            throw new NotFoundException("Menu not found");
        }
        return $menu;
    }

    /**
     * Get menu by location
     */
    public function getMenuByLocation(string $location): ?array
    {
        return $this->repository->findByLocation($location);
    }

    /**
     * Get menu with items
     */
    public function getMenuWithItems(string $menuId): array
    {
        $menu = $this->getMenuById($menuId);
        $menu['items'] = $this->repository->getMenuItemsTree($menuId);
        return $menu;
    }

    /**
     * Create menu
     */
    public function createMenu(array $data): string
    {
        if (empty($data['name'])) {
            throw new ValidationException("Menu name is required");
        }
        
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        return $this->repository->create($data);
    }

    /**
     * Update menu
     */
    public function updateMenu(string $id, array $data): void
    {
        $this->getMenuById($id); // Verify exists
        $this->repository->update($id, $data);
    }

    /**
     * Delete menu
     */
    public function deleteMenu(string $id): void
    {
        $this->getMenuById($id); // Verify exists
        $this->repository->delete($id);
    }

    /**
     * Create menu item
     */
    public function createMenuItem(array $data): string
    {
        if (empty($data['title'])) {
            throw new ValidationException("Menu item title is required");
        }
        
        if (empty($data['menu_id'])) {
            throw new ValidationException("Menu ID is required");
        }
        
        return $this->repository->createMenuItem($data);
    }

    /**
     * Update menu item
     */
    public function updateMenuItem(string $id, array $data): void
    {
        $this->repository->updateMenuItem($id, $data);
    }

    /**
     * Delete menu item
     */
    public function deleteMenuItem(string $id): void
    {
        $this->repository->deleteMenuItem($id);
    }

    /**
     * Update menu items order
     */
    public function updateMenuItemsOrder(array $items): void
    {
        $this->repository->updateMenuItemsOrder($items);
    }

    /**
     * Generate slug from name
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}

