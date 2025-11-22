<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class HomepageSectionRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(?string $pageId = null): array
    {
        if ($pageId) {
            $statement = $this->pdo->prepare('SELECT * FROM homepage_sections WHERE page_id = :page_id ORDER BY order_index ASC, createdAt ASC');
            $statement->execute([':page_id' => $pageId]);
        } else {
            $statement = $this->pdo->query('SELECT * FROM homepage_sections WHERE page_id IS NULL ORDER BY order_index ASC, createdAt ASC');
        }
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'transform'], $results);
    }

    public function active(?string $pageId = null): array
    {
        if ($pageId) {
            $statement = $this->pdo->prepare('SELECT * FROM homepage_sections WHERE page_id = :page_id AND status = "ACTIVE" ORDER BY order_index ASC, createdAt ASC');
            $statement->execute([':page_id' => $pageId]);
        } else {
            $statement = $this->pdo->query('SELECT * FROM homepage_sections WHERE page_id IS NULL AND status = "ACTIVE" ORDER BY order_index ASC, createdAt ASC');
        }
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'transform'], $results);
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM homepage_sections WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->transform($result) : null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('section');

        $sql = <<<SQL
INSERT INTO homepage_sections (
    id, page_id, section_type, title, content, order_index, status, settings, createdAt, updatedAt
) VALUES (
    :id, :page_id, :section_type, :title, :content, :order_index, :status, :settings, NOW(), NOW()
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
            throw new RuntimeException('Homepage section not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));

        $sql = <<<SQL
UPDATE homepage_sections SET
    page_id = :page_id,
    section_type = :section_type,
    title = :title,
    content = :content,
    order_index = :order_index,
    status = :status,
    settings = :settings,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':page_id' => $data['page_id'],
            ':section_type' => $data['section_type'],
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':order_index' => (int) $data['order_index'],
            ':status' => $data['status'],
            ':settings' => $data['settings'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function updateOrder(array $sections): void
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($sections as $index => $sectionId) {
                $statement = $this->pdo->prepare('UPDATE homepage_sections SET order_index = :order_index WHERE id = :id');
                $statement->execute([
                    ':id' => $sectionId,
                    ':order_index' => $index,
                ]);
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Failed to update section order: ' . $e->getMessage());
        }
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM homepage_sections WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        return [
            'page_id' => $attributes['page_id'] ?? null,
            'section_type' => $attributes['section_type'] ?? 'custom',
            'title' => $attributes['title'] ?? null,
            'content' => json_encode($attributes['content'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'order_index' => (int) ($attributes['order_index'] ?? 0),
            'status' => $attributes['status'] ?? 'ACTIVE',
            'settings' => json_encode($attributes['settings'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    private function transform(array $section): array
    {
        $section['content'] = json_decode($section['content'] ?? '[]', true);
        $section['settings'] = json_decode($section['settings'] ?? '[]', true);
        return $section;
    }
}

