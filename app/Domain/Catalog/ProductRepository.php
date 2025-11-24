<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Support\Id;
use PDO;
use RuntimeException;

final class ProductRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function featured(int $limit = 6): array
    {
        $sql = <<<SQL
SELECT p.*, c.name AS category_name, c.slug AS category_slug
FROM products p
LEFT JOIN categories c ON p.categoryId = c.id
WHERE p.status = 'PUBLISHED'
ORDER BY p.updatedAt DESC
LIMIT :limit
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function paginate(?string $categorySlug = null, int $limit = 50, int $offset = 0): array
    {
        $params = [
            ':limit'  => $limit,
            ':offset' => $offset,
        ];

        if ($categorySlug) {
            $sql = <<<SQL
SELECT p.*, c.name AS category_name, c.slug AS category_slug
FROM products p
LEFT JOIN categories c ON p.categoryId = c.id
WHERE p.status = 'PUBLISHED' AND c.slug = :slug
ORDER BY p.updatedAt DESC
LIMIT :limit OFFSET :offset
SQL;
            $params[':slug'] = $categorySlug;
        } else {
            $sql = <<<SQL
SELECT p.*, c.name AS category_name, c.slug AS category_slug
FROM products p
LEFT JOIN categories c ON p.categoryId = c.id
WHERE p.status = 'PUBLISHED'
ORDER BY p.updatedAt DESC
LIMIT :limit OFFSET :offset
SQL;
        }

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $type = $key === ':slug' ? PDO::PARAM_STR : PDO::PARAM_INT;
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function all(array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'p.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['categoryId'])) {
            $conditions[] = 'p.categoryId = :categoryId';
            $params[':categoryId'] = $filters['categoryId'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = <<<SQL
SELECT p.*, c.name AS category_name, c.slug AS category_slug
FROM products p
LEFT JOIN categories c ON p.categoryId = c.id
$where
ORDER BY p.updatedAt DESC
SQL;

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        $statement->execute();

        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function paginateForAdmin(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $conditions = [];
        $params = [
            ':limit'  => $limit,
            ':offset' => $offset,
        ];

        if (!empty($filters['status'])) {
            $conditions[] = 'p.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['categoryId'])) {
            $conditions[] = 'p.categoryId = :categoryId';
            $params[':categoryId'] = $filters['categoryId'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = <<<SQL
SELECT p.*, c.name AS category_name, c.slug AS category_slug
FROM products p
LEFT JOIN categories c ON p.categoryId = c.id
$where
ORDER BY p.updatedAt DESC
LIMIT :limit OFFSET :offset
SQL;

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $type = in_array($key, [':limit', ':offset'], true) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findBySlug(string $slug): ?array
    {
        $sql = <<<SQL
SELECT p.*, c.name AS category_name, c.slug AS category_slug
FROM products p
LEFT JOIN categories c ON p.categoryId = c.id
WHERE p.slug = :slug
LIMIT 1
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([':slug' => $slug]);
        $product = $statement->fetch(PDO::FETCH_ASSOC);

        return $product ? $this->transform($product) : null;
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $product = $statement->fetch(PDO::FETCH_ASSOC);

        return $product ? $this->transform($product) : null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('prod');

        $sql = <<<SQL
INSERT INTO products (
    id, name, slug, sku, summary, description, specs, heroImage, price, status,
    highlights, categoryId, createdAt, updatedAt
) VALUES (
    :id, :name, :slug, :sku, :summary, :description, :specs, :heroImage, :price, :status,
    :highlights, :categoryId, NOW(), NOW()
)
SQL;

        $statement = $this->pdo->prepare($sql);

        if (!$statement->execute($data)) {
            throw new RuntimeException('Failed to create product.');
        }

        return $this->findById($data['id']) ?? $data;
    }

    public function update(string $id, array $attributes): array
    {
        $existing = $this->findById($id);

        if (!$existing) {
            throw new RuntimeException('Product not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));

        $sql = <<<SQL
UPDATE products SET
    name = :name,
    slug = :slug,
    sku = :sku,
    summary = :summary,
    description = :description,
    specs = :specs,
    heroImage = :heroImage,
    price = :price,
    status = :status,
    highlights = :highlights,
    categoryId = :categoryId,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            ':id'          => $id,
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':sku'         => $data['sku'],
            ':summary'     => $data['summary'],
            ':description' => $data['description'],
            ':specs'       => $data['specs'],
            ':heroImage'   => $data['heroImage'],
            ':price'       => $data['price'],
            ':status'      => $data['status'],
            ':highlights'  => $data['highlights'],
            ':categoryId'  => $data['categoryId'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function updateStatus(string $id, string $status): void
    {
        $statement = $this->pdo->prepare('UPDATE products SET status = :status, updatedAt = NOW() WHERE id = :id');
        $statement->execute([
            ':id'     => $id,
            ':status' => $status,
        ]);
    }

    public function delete(string $id): void
    {
        $existing = $this->findById($id);

        if (!$existing) {
            throw new RuntimeException('Product not found.');
        }

        $statement = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        return [
            'id'         => $attributes['id'] ?? null,
            'name'       => $attributes['name'] ?? '',
            'slug'       => $attributes['slug'] ?? '',
            'sku'        => $attributes['sku'] ?? null,
            'summary'    => $attributes['summary'] ?? null,
            'description'=> $attributes['description'] ?? null,
            'specs'      => $this->encodeJson($attributes['specs'] ?? null),
            'heroImage'  => $attributes['heroImage'] ?? null,
            'price'      => $attributes['price'] !== null ? (float) $attributes['price'] : null,
            'status'     => $attributes['status'] ?? 'DRAFT',
            'highlights' => $this->encodeJson($attributes['highlights'] ?? null),
            'categoryId' => $attributes['categoryId'] ?? null,
        ];
    }

    private function transform(array $product): array
    {
        $product['specs'] = $this->decodeJson($product['specs'] ?? null);
        $product['highlights'] = $this->decodeJson($product['highlights'] ?? null);

        return $product;
    }

    private function encodeJson(null|array|string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function decodeJson(?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}

