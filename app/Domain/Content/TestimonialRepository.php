<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class TestimonialRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM testimonials ORDER BY priority DESC, createdAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function published(): array
    {
        $statement = $this->pdo->query('SELECT * FROM testimonials WHERE status = "PUBLISHED" ORDER BY priority DESC, createdAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function featured(int $limit = 6): array
    {
        $sql = 'SELECT * FROM testimonials WHERE status = "PUBLISHED" AND featured = 1 ORDER BY priority DESC, createdAt DESC LIMIT :limit';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('test');

        $sql = <<<SQL
INSERT INTO testimonials (
    id, name, company, position, content, rating, avatar, featured, priority, status, createdAt, updatedAt
) VALUES (
    :id, :name, :company, :position, :content, :rating, :avatar, :featured, :priority, :status, NOW(), NOW()
)
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute($data);

        return $this->findById($data['id']) ?? $data;
    }

    public function update(string $id, array $attributes): array
    {
        $existing = $this->findById($id);
        if (!$existing) {
            throw new RuntimeException('Testimonial not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));

        $sql = <<<SQL
UPDATE testimonials SET
    name = :name,
    company = :company,
    position = :position,
    content = :content,
    rating = :rating,
    avatar = :avatar,
    featured = :featured,
    priority = :priority,
    status = :status,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':company' => $data['company'],
            ':position' => $data['position'],
            ':content' => $data['content'],
            ':rating' => (int) $data['rating'],
            ':avatar' => $data['avatar'],
            ':featured' => $data['featured'] ? 1 : 0,
            ':priority' => (int) $data['priority'],
            ':status' => $data['status'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM testimonials WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        return [
            'name'     => $attributes['name'] ?? '',
            'company'  => $attributes['company'] ?? null,
            'position' => $attributes['position'] ?? null,
            'content'  => $attributes['content'] ?? '',
            'rating'   => isset($attributes['rating']) ? max(1, min(5, (int) $attributes['rating'])) : 5,
            'avatar'   => $attributes['avatar'] ?? null,
            'featured' => isset($attributes['featured']) ? (bool) $attributes['featured'] : false,
            'priority' => (int) ($attributes['priority'] ?? 0),
            'status'   => $attributes['status'] ?? 'DRAFT',
        ];
    }
}

