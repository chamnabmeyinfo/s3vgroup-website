<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class SliderRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM sliders ORDER BY priority DESC, createdAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function published(): array
    {
        $statement = $this->pdo->query('SELECT * FROM sliders WHERE status = "PUBLISHED" ORDER BY priority DESC, createdAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM sliders WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('slider');

        $sql = <<<SQL
INSERT INTO sliders (
    id, title, subtitle, description, image_url, link_url, link_text, button_color, priority, status, createdAt, updatedAt
) VALUES (
    :id, :title, :subtitle, :description, :image_url, :link_url, :link_text, :button_color, :priority, :status, NOW(), NOW()
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
            throw new RuntimeException('Slider not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));

        $sql = <<<SQL
UPDATE sliders SET
    title = :title,
    subtitle = :subtitle,
    description = :description,
    image_url = :image_url,
    link_url = :link_url,
    link_text = :link_text,
    button_color = :button_color,
    priority = :priority,
    status = :status,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':subtitle' => $data['subtitle'],
            ':description' => $data['description'],
            ':image_url' => $data['image_url'],
            ':link_url' => $data['link_url'],
            ':link_text' => $data['link_text'],
            ':button_color' => $data['button_color'],
            ':priority' => (int) $data['priority'],
            ':status' => $data['status'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM sliders WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        return [
            'title'       => $attributes['title'] ?? '',
            'subtitle'    => $attributes['subtitle'] ?? null,
            'description' => $attributes['description'] ?? null,
            'image_url'   => $attributes['image_url'] ?? '',
            'link_url'    => $attributes['link_url'] ?? null,
            'link_text'   => $attributes['link_text'] ?? null,
            'button_color'=> $attributes['button_color'] ?? '#0b3a63',
            'priority'    => (int) ($attributes['priority'] ?? 0),
            'status'      => $attributes['status'] ?? 'DRAFT',
        ];
    }
}

