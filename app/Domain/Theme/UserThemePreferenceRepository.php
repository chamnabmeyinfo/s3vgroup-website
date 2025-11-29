<?php

declare(strict_types=1);

namespace App\Domain\Theme;

use App\Support\Id;
use PDO;

final class UserThemePreferenceRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Find user theme preference by user ID and scope
     *
     * @return array<string, mixed>|null
     */
    public function findByUserAndScope(string $userId, string $scope = 'public_frontend'): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM user_theme_preferences WHERE user_id = :user_id AND scope = :scope LIMIT 1'
        );
        $statement->execute([
            ':user_id' => $userId,
            ':scope' => $scope,
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Get all preferences for a user
     *
     * @return array<int, array<string, mixed>>
     */
    public function findByUser(string $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM user_theme_preferences WHERE user_id = :user_id ORDER BY scope ASC'
        );
        $statement->execute([':user_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create or update user theme preference
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function upsert(array $attributes): array
    {
        $data = $this->normalize($attributes);

        $existing = $this->findByUserAndScope($data['user_id'], $data['scope']);

        if ($existing) {
            // Update existing
            $sql = <<<SQL
UPDATE user_theme_preferences SET
    theme_id = :theme_id,
    updatedAt = NOW()
WHERE user_id = :user_id AND scope = :scope
SQL;

            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                ':user_id' => $data['user_id'],
                ':scope' => $data['scope'],
                ':theme_id' => $data['theme_id'],
            ]);

            return $this->findByUserAndScope($data['user_id'], $data['scope']) ?? $data;
        }

        // Create new
        $data['id'] = $data['id'] ?? Id::prefixed('utp');

        $sql = <<<SQL
INSERT INTO user_theme_preferences (
    id, user_id, theme_id, scope, createdAt, updatedAt
) VALUES (
    :id, :user_id, :theme_id, :scope, NOW(), NOW()
)
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $data['id'],
            ':user_id' => $data['user_id'],
            ':theme_id' => $data['theme_id'],
            ':scope' => $data['scope'],
        ]);

        return $this->findByUserAndScope($data['user_id'], $data['scope']) ?? $data;
    }

    /**
     * Delete user theme preference
     */
    public function delete(string $userId, string $scope = 'public_frontend'): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM user_theme_preferences WHERE user_id = :user_id AND scope = :scope'
        );
        $statement->execute([
            ':user_id' => $userId,
            ':scope' => $scope,
        ]);
    }

    /**
     * Get theme for user preference (with theme data joined)
     *
     * @return array<string, mixed>|null
     */
    public function getThemeForUser(string $userId, string $scope = 'public_frontend'): ?array
    {
        $sql = <<<SQL
SELECT t.*
FROM user_theme_preferences utp
INNER JOIN themes t ON utp.theme_id = t.id
WHERE utp.user_id = :user_id AND utp.scope = :scope AND t.is_active = 1
LIMIT 1
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':user_id' => $userId,
            ':scope' => $scope,
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        // Transform theme data
        $result['is_default'] = (bool) $result['is_default'];
        $result['is_active'] = (bool) $result['is_active'];

        if (isset($result['config']) && is_string($result['config'])) {
            $result['config'] = json_decode($result['config'], true) ?? [];
        }

        return $result;
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
            'user_id' => $attributes['user_id'] ?? null,
            'theme_id' => $attributes['theme_id'] ?? null,
            'scope' => $attributes['scope'] ?? 'public_frontend',
        ];
    }
}

