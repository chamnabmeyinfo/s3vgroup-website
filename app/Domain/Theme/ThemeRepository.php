<?php

declare(strict_types=1);

namespace App\Domain\Theme;

use App\Domain\Exceptions\NotFoundException;
use App\Support\Id;
use PDO;

final class ThemeRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Get all themes
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function all(array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (isset($filters['is_active']) && $filters['is_active'] !== null) {
            $conditions[] = 'is_active = :is_active';
            $params[':is_active'] = $filters['is_active'] ? 1 : 0;
        }

        if (isset($filters['is_default']) && $filters['is_default'] !== null) {
            $conditions[] = 'is_default = :is_default';
            $params[':is_default'] = $filters['is_default'] ? 1 : 0;
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT * FROM themes {$whereClause} ORDER BY is_default DESC, name ASC";
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get active themes only (for public consumption)
     *
     * @return array<int, array<string, mixed>>
     */
    public function active(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name, slug, description, config FROM themes WHERE is_active = 1 ORDER BY is_default DESC, name ASC'
        );
        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Find theme by ID
     *
     * @return array<string, mixed>|null
     */
    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM themes WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ? $this->transform($result) : null;
    }

    /**
     * Find theme by slug
     *
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM themes WHERE slug = :slug LIMIT 1');
        $statement->execute([':slug' => $slug]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ? $this->transform($result) : null;
    }

    /**
     * Get the default theme
     *
     * @return array<string, mixed>|null
     */
    public function getDefault(): ?array
    {
        $statement = $this->pdo->query(
            'SELECT * FROM themes WHERE is_default = 1 AND is_active = 1 LIMIT 1'
        );
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ? $this->transform($result) : null;
    }

    /**
     * Check if slug exists (excluding current theme)
     *
     * @return bool
     */
    public function slugExists(string $slug, ?string $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM themes WHERE slug = :slug';
        $params = [':slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * Create a new theme
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('theme');

        $sql = <<<SQL
INSERT INTO themes (
    id, name, slug, description, is_default, is_active, config, createdAt, updatedAt
) VALUES (
    :id, :name, :slug, :description, :is_default, :is_active, :config, NOW(), NOW()
)
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'],
            ':is_default' => $data['is_default'] ? 1 : 0,
            ':is_active' => $data['is_active'] ? 1 : 0,
            ':config' => json_encode($data['config'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return $this->findById($data['id']) ?? $data;
    }

    /**
     * Update a theme
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function update(string $id, array $attributes): array
    {
        $existing = $this->findById($id);

        if (!$existing) {
            throw new NotFoundException('Theme not found.');
        }

        // Only normalize fields that are actually being updated
        $normalized = [];
        
        // Only include fields that are present in attributes (partial update)
        if (isset($attributes['name'])) {
            $normalized['name'] = $attributes['name'];
        }
        if (isset($attributes['slug'])) {
            $normalized['slug'] = $attributes['slug'];
        }
        if (isset($attributes['description'])) {
            $normalized['description'] = $attributes['description'] !== null ? (string) $attributes['description'] : null;
        }
        if (isset($attributes['is_default'])) {
            $normalized['is_default'] = (bool) $attributes['is_default'];
        }
        if (isset($attributes['is_active'])) {
            $normalized['is_active'] = (bool) $attributes['is_active'];
        }
        
        // Merge config deeply if provided
        if (isset($attributes['config'])) {
            // Ensure config is an array
            if (!is_array($attributes['config'])) {
                $normalized['config'] = [];
            } else {
                $existingConfig = [];
                if (isset($existing['config'])) {
                    if (is_string($existing['config'])) {
                        $decoded = json_decode($existing['config'], true);
                        $existingConfig = is_array($decoded) ? $decoded : [];
                    } elseif (is_array($existing['config'])) {
                        $existingConfig = $existing['config'];
                    }
                }
                
                // Merge top-level config
                $mergedConfig = array_merge($existingConfig, $attributes['config']);
                
                // Merge nested arrays properly (not using array_merge_recursive to avoid issues)
                foreach (['colors', 'typography', 'radius', 'shadows'] as $key) {
                    if (isset($attributes['config'][$key]) && is_array($attributes['config'][$key])) {
                        $mergedConfig[$key] = array_merge(
                            $existingConfig[$key] ?? [],
                            $attributes['config'][$key]
                        );
                    } elseif (!isset($mergedConfig[$key]) && isset($existingConfig[$key])) {
                        $mergedConfig[$key] = $existingConfig[$key];
                    }
                }
                
                $normalized['config'] = $mergedConfig;
            }
        }

        // Merge with existing data, but only update fields that were provided
        $data = array_merge($existing, $normalized);

        // Build dynamic SQL to only update provided fields
        $updates = [];
        $params = [':id' => $id];
        
        if (isset($normalized['name'])) {
            $updates[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        if (isset($normalized['slug'])) {
            $updates[] = 'slug = :slug';
            $params[':slug'] = $data['slug'];
        }
        if (isset($normalized['description'])) {
            $updates[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        if (isset($normalized['is_default'])) {
            $updates[] = 'is_default = :is_default';
            $params[':is_default'] = $data['is_default'] ? 1 : 0;
        }
        if (isset($normalized['is_active'])) {
            $updates[] = 'is_active = :is_active';
            $params[':is_active'] = $data['is_active'] ? 1 : 0;
        }
        if (isset($normalized['config'])) {
            $updates[] = 'config = :config';
            $params[':config'] = json_encode($data['config'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        // Always update updatedAt
        $updates[] = 'updatedAt = NOW()';
        
        if (empty($updates)) {
            // No fields to update, return existing
            return $existing;
        }

        $sql = 'UPDATE themes SET ' . implode(', ', $updates) . ' WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $this->findById($id) ?? $data;
    }

    /**
     * Set a theme as default (and unset others)
     */
    public function setAsDefault(string $id): void
    {
        $this->pdo->beginTransaction();

        try {
            // Unset all defaults
            $this->pdo->exec('UPDATE themes SET is_default = 0');

            // Set this theme as default
            $statement = $this->pdo->prepare('UPDATE themes SET is_default = 1, updatedAt = NOW() WHERE id = :id');
            $statement->execute([':id' => $id]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Delete a theme (soft delete by setting is_active = false)
     */
    public function delete(string $id): void
    {
        $existing = $this->findById($id);

        if (!$existing) {
            throw new NotFoundException('Theme not found.');
        }

        // Soft delete: set is_active = false
        $statement = $this->pdo->prepare('UPDATE themes SET is_active = 0, updatedAt = NOW() WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    /**
     * Count active themes
     */
    public function countActive(): int
    {
        $statement = $this->pdo->query('SELECT COUNT(*) FROM themes WHERE is_active = 1');
        return (int) $statement->fetchColumn();
    }

    /**
     * Count default themes
     */
    public function countDefault(): int
    {
        $statement = $this->pdo->query('SELECT COUNT(*) FROM themes WHERE is_default = 1');
        return (int) $statement->fetchColumn();
    }

    /**
     * Transform database row to array with proper types
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transform(array $row): array
    {
        $row['is_default'] = (bool) $row['is_default'];
        $row['is_active'] = (bool) $row['is_active'];

        // Parse JSON config
        if (isset($row['config']) && is_string($row['config'])) {
            $row['config'] = json_decode($row['config'], true) ?? [];
        }

        return $row;
    }

    /**
     * Normalize attributes for database
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function normalize(array $attributes): array
    {
        return [
            'id' => $attributes['id'] ?? null,
            'name' => $attributes['name'] ?? null,
            'slug' => $attributes['slug'] ?? null,
            'description' => $attributes['description'] ?? null,
            'is_default' => isset($attributes['is_default']) ? (bool) $attributes['is_default'] : false,
            'is_active' => isset($attributes['is_active']) ? (bool) $attributes['is_active'] : true,
            'config' => $attributes['config'] ?? [],
        ];
    }
}

