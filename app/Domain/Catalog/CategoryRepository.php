<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Support\Id;
use PDO;
use PDOException;
use RuntimeException;

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM categories ORDER BY priority DESC, name ASC');

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function featured(int $limit = 6): array
    {
        $sql = 'SELECT * FROM categories ORDER BY priority DESC, name ASC LIMIT :limit';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
        $statement->execute([':slug' => $slug]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('cat');

        $sql = 'INSERT INTO categories (id, name, slug, description, icon, priority, createdAt, updatedAt)
                VALUES (:id, :name, :slug, :description, :icon, :priority, NOW(), NOW())';

        $statement = $this->pdo->prepare($sql);

        if (!$statement->execute($data)) {
            throw new RuntimeException('Failed to create category.');
        }

        return $this->findById($data['id']) ?? $data;
    }

    public function update(string $id, array $attributes): array
    {
        $existing = $this->findById($id);

        if (!$existing) {
            throw new RuntimeException('Category not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));
        $sql = 'UPDATE categories SET name = :name, slug = :slug, description = :description, icon = :icon, priority = :priority, updatedAt = NOW() WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id'          => $id,
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':description' => $data['description'],
            ':icon'        => $data['icon'],
            ':priority'    => (int) $data['priority'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function delete(string $id): void
    {
        $existing = $this->findById($id);

        if (!$existing) {
            throw new RuntimeException('Category not found.');
        }

        // Check if category has products
        $check = $this->pdo->prepare('SELECT COUNT(*) as count FROM products WHERE categoryId = :id');
        $check->execute([':id' => $id]);
        $result = $check->fetch(PDO::FETCH_ASSOC);

        if ((int) ($result['count'] ?? 0) > 0) {
            throw new RuntimeException('Cannot delete category with existing products.');
        }

        $statement = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    private function normalize(array $attributes): array
    {
        return [
            'id'          => $attributes['id'] ?? null,
            'name'        => $attributes['name'] ?? '',
            'slug'        => $attributes['slug'] ?? '',
            'description' => $attributes['description'] ?? null,
            'icon'        => $attributes['icon'] ?? null,
            'priority'    => (int) ($attributes['priority'] ?? 0),
        ];
    }
}

