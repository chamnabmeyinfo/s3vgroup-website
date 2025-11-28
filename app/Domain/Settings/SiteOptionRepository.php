<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Domain\Exceptions\NotFoundException;
use App\Support\Id;
use PDO;

final class SiteOptionRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM site_options ORDER BY group_name, priority DESC, label ASC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function byGroup(string $group): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM site_options WHERE group_name = :group ORDER BY priority DESC, label ASC');
        $statement->execute([':group' => $group]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(string $key, $default = null)
    {
        $statement = $this->pdo->prepare('SELECT value, type FROM site_options WHERE key_name = :key LIMIT 1');
        $statement->execute([':key' => $key]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return $default;
        }

        return $this->castValue($result['value'], $result['type']);
    }

    public function set(string $key, $value, ?string $type = null): void
    {
        $existing = $this->findByKey($key);
        
        if ($type === null) {
            $type = $existing['type'] ?? 'text';
        }

        $normalized = $this->normalizeValue($value, $type);

        if ($existing) {
            // Update existing option
            $statement = $this->pdo->prepare('UPDATE site_options SET value = :value, updatedAt = NOW() WHERE key_name = :key');
            $statement->execute([
                ':key' => $key,
                ':value' => $normalized,
            ]);
        } else {
            // Create new option
            $this->create([
                'key_name' => $key,
                'value' => $value,
                'type' => $type,
                'group_name' => 'advanced',
                'label' => ucwords(str_replace('_', ' ', $key)),
                'description' => '',
                'priority' => 0,
            ]);
        }
    }

    public function findByKey(string $key): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM site_options WHERE key_name = :key LIMIT 1');
        $statement->execute([':key' => $key]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $result['value'] = $this->castValue($result['value'], $result['type']);
        }

        return $result ?: null;
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM site_options WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $result['value'] = $this->castValue($result['value'], $result['type']);
        }

        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('opt');
        $data['value'] = $this->normalizeValue($data['value'] ?? '', $data['type']);

        $sql = <<<SQL
INSERT INTO site_options (
    id, key_name, value, type, group_name, label, description, priority, createdAt, updatedAt
) VALUES (
    :id, :key_name, :value, :type, :group_name, :label, :description, :priority, NOW(), NOW()
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
            throw new NotFoundException('Site option not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));
        
        if (isset($attributes['value'])) {
            $data['value'] = $this->normalizeValue($attributes['value'], $data['type']);
        }

        $sql = <<<SQL
UPDATE site_options SET
    key_name = :key_name,
    value = :value,
    type = :type,
    group_name = :group_name,
    label = :label,
    description = :description,
    priority = :priority,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':key_name' => $data['key_name'],
            ':value' => $data['value'],
            ':type' => $data['type'],
            ':group_name' => $data['group_name'],
            ':label' => $data['label'],
            ':description' => $data['description'],
            ':priority' => (int) $data['priority'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function bulkUpdate(array $options): void
    {
        $this->pdo->beginTransaction();

        try {
            foreach ($options as $key => $value) {
                $this->set($key, $value);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function normalize(array $attributes): array
    {
        return [
            'key_name'   => $attributes['key_name'] ?? null,
            'value'      => $attributes['value'] ?? null,
            'type'       => $attributes['type'] ?? 'text',
            'group_name' => $attributes['group_name'] ?? 'general',
            'label'      => $attributes['label'] ?? null,
            'description'=> $attributes['description'] ?? null,
            'priority'   => (int) ($attributes['priority'] ?? 0),
        ];
    }

    private function normalizeValue($value, string $type): string
    {
        if ($value === null) {
            return '';
        }

        if ($type === 'json' || $type === 'boolean') {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }

    private function castValue(?string $value, string $type)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'number'  => is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : 0,
            'boolean' => json_decode($value, true) ?? filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}

