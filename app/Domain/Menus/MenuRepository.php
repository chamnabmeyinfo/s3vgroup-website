<?php

declare(strict_types=1);

namespace App\Domain\Menus;

use PDO;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Exceptions\DatabaseException;

class MenuRepository
{
    public function __construct(
        private PDO $db
    ) {}

    /**
     * Get all menus
     */
    public function all(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM menus 
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to fetch menus: " . $e->getMessage());
        }
    }

    /**
     * Get menu by ID
     */
    public function findById(string $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            $menu = $stmt->fetch(PDO::FETCH_ASSOC);
            return $menu ?: null;
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to fetch menu: " . $e->getMessage());
        }
    }

    /**
     * Get menu by slug
     */
    public function findBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM menus WHERE slug = ?");
            $stmt->execute([$slug]);
            $menu = $stmt->fetch(PDO::FETCH_ASSOC);
            return $menu ?: null;
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to fetch menu: " . $e->getMessage());
        }
    }

    /**
     * Get menu by location
     */
    public function findByLocation(string $location): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM menus WHERE location = ? LIMIT 1");
            $stmt->execute([$location]);
            $menu = $stmt->fetch(PDO::FETCH_ASSOC);
            return $menu ?: null;
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to fetch menu: " . $e->getMessage());
        }
    }

    /**
     * Create menu
     */
    public function create(array $data): string
    {
        try {
            $id = $data['id'] ?? 'menu-' . uniqid();
            $stmt = $this->db->prepare("
                INSERT INTO menus (id, name, slug, location, description)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $data['name'] ?? '',
                $data['slug'] ?? '',
                $data['location'] ?? 'primary',
                $data['description'] ?? null,
            ]);
            return $id;
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to create menu: " . $e->getMessage());
        }
    }

    /**
     * Update menu
     */
    public function update(string $id, array $data): void
    {
        try {
            $fields = [];
            $values = [];
            
            if (isset($data['name'])) {
                $fields[] = 'name = ?';
                $values[] = $data['name'];
            }
            if (isset($data['slug'])) {
                $fields[] = 'slug = ?';
                $values[] = $data['slug'];
            }
            if (isset($data['location'])) {
                $fields[] = 'location = ?';
                $values[] = $data['location'];
            }
            if (isset($data['description'])) {
                $fields[] = 'description = ?';
                $values[] = $data['description'];
            }
            
            if (empty($fields)) {
                return;
            }
            
            $values[] = $id;
            $sql = "UPDATE menus SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to update menu: " . $e->getMessage());
        }
    }

    /**
     * Delete menu
     */
    public function delete(string $id): void
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM menus WHERE id = ?");
            $stmt->execute([$id]);
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to delete menu: " . $e->getMessage());
        }
    }

    /**
     * Get menu items for a menu
     */
    public function getMenuItems(string $menuId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM menu_items 
                WHERE menu_id = ? 
                ORDER BY menu_order ASC, created_at ASC
            ");
            $stmt->execute([$menuId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to fetch menu items: " . $e->getMessage());
        }
    }

    /**
     * Get menu items tree (with hierarchy)
     */
    public function getMenuItemsTree(string $menuId): array
    {
        $items = $this->getMenuItems($menuId);
        return $this->buildTree($items);
    }

    /**
     * Build tree structure from flat array
     */
    private function buildTree(array $items, ?string $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * Create menu item
     */
    public function createMenuItem(array $data): string
    {
        try {
            $id = $data['id'] ?? 'menu-item-' . uniqid();
            $stmt = $this->db->prepare("
                INSERT INTO menu_items (
                    id, menu_id, parent_id, title, url, type, object_id, object_type,
                    menu_order, css_classes, description, icon, target,
                    is_mega_menu, mega_menu_columns, mega_menu_image, mega_menu_content
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $data['menu_id'] ?? '',
                $data['parent_id'] ?? null,
                $data['title'] ?? '',
                $data['url'] ?? '#',
                $data['type'] ?? 'custom',
                $data['object_id'] ?? null,
                $data['object_type'] ?? null,
                $data['menu_order'] ?? 0,
                $data['css_classes'] ?? null,
                $data['description'] ?? null,
                $data['icon'] ?? null,
                $data['target'] ?? '_self',
                $data['is_mega_menu'] ?? 0,
                $data['mega_menu_columns'] ?? 3,
                $data['mega_menu_image'] ?? null,
                $data['mega_menu_content'] ?? null,
            ]);
            return $id;
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to create menu item: " . $e->getMessage());
        }
    }

    /**
     * Update menu item
     */
    public function updateMenuItem(string $id, array $data): void
    {
        try {
            $fields = [];
            $values = [];
            
            $allowedFields = [
                'title', 'url', 'type', 'object_id', 'object_type', 'menu_order',
                'css_classes', 'description', 'icon', 'target', 'parent_id',
                'is_mega_menu', 'mega_menu_columns', 'mega_menu_image', 'mega_menu_content'
            ];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return;
            }
            
            $values[] = $id;
            $sql = "UPDATE menu_items SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to update menu item: " . $e->getMessage());
        }
    }

    /**
     * Delete menu item
     */
    public function deleteMenuItem(string $id): void
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to delete menu item: " . $e->getMessage());
        }
    }

    /**
     * Update menu items order
     */
    public function updateMenuItemsOrder(array $items): void
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE menu_items SET menu_order = ?, parent_id = ? WHERE id = ?");
            
            foreach ($items as $order => $item) {
                $stmt->execute([
                    $order,
                    $item['parent_id'] ?? null,
                    $item['id']
                ]);
            }
            
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw new DatabaseException("Failed to update menu items order: " . $e->getMessage());
        }
    }
}

