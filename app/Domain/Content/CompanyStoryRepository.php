<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class CompanyStoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(): ?array
    {
        $statement = $this->pdo->query('SELECT * FROM company_story ORDER BY updatedAt DESC LIMIT 1');
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            // Decode JSON fields
            $result['values'] = $result['values'] ? json_decode($result['values'], true) : null;
            $result['milestones'] = $result['milestones'] ? json_decode($result['milestones'], true) : null;
        }
        return $result ?: null;
    }

    public function published(): ?array
    {
        $statement = $this->pdo->query('SELECT * FROM company_story WHERE status = "PUBLISHED" ORDER BY updatedAt DESC LIMIT 1');
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            // Decode JSON fields
            $result['values'] = $result['values'] ? json_decode($result['values'], true) : null;
            $result['milestones'] = $result['milestones'] ? json_decode($result['milestones'], true) : null;
        }
        return $result ?: null;
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM company_story WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            // Decode JSON fields
            $result['values'] = $result['values'] ? json_decode($result['values'], true) : null;
            $result['milestones'] = $result['milestones'] ? json_decode($result['milestones'], true) : null;
        }
        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('story');

        $sql = <<<SQL
INSERT INTO company_story (
    id, title, subtitle, heroImage, introduction, history, mission, vision,
    values, milestones, achievements, status, createdAt, updatedAt
) VALUES (
    :id, :title, :subtitle, :heroImage, :introduction, :history, :mission, :vision,
    :values, :milestones, :achievements, :status, NOW(), NOW()
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
            throw new RuntimeException('Company story not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));

        $sql = <<<SQL
UPDATE company_story SET
    title = :title,
    subtitle = :subtitle,
    heroImage = :heroImage,
    introduction = :introduction,
    history = :history,
    mission = :mission,
    vision = :vision,
    values = :values,
    milestones = :milestones,
    achievements = :achievements,
    status = :status,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':subtitle' => $data['subtitle'],
            ':heroImage' => $data['heroImage'],
            ':introduction' => $data['introduction'],
            ':history' => $data['history'],
            ':mission' => $data['mission'],
            ':vision' => $data['vision'],
            ':values' => $data['values'],
            ':milestones' => $data['milestones'],
            ':achievements' => $data['achievements'],
            ':status' => $data['status'],
        ]);

        return $this->findById($id) ?? $data;
    }

    private function normalize(array $attributes): array
    {
        $values = $attributes['values'] ?? [];
        $milestones = $attributes['milestones'] ?? [];

        return [
            'title'        => $attributes['title'] ?? 'Our Company Story',
            'subtitle'     => $attributes['subtitle'] ?? null,
            'heroImage'    => $attributes['heroImage'] ?? null,
            'introduction' => $attributes['introduction'] ?? null,
            'history'      => $attributes['history'] ?? null,
            'mission'      => $attributes['mission'] ?? null,
            'vision'       => $attributes['vision'] ?? null,
            'values'       => !empty($values) ? json_encode($values, JSON_UNESCAPED_UNICODE) : null,
            'milestones'   => !empty($milestones) ? json_encode($milestones, JSON_UNESCAPED_UNICODE) : null,
            'achievements' => $attributes['achievements'] ?? null,
            'status'       => $attributes['status'] ?? 'DRAFT',
        ];
    }
}

