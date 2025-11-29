<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/**
 * Plugin Registry - Manages plugin metadata and status
 */
final class PluginRegistry
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    /**
     * Ensure plugins table exists
     */
    private function ensureTableExists(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS plugins (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    version VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    path VARCHAR(500) NOT NULL,
    metadata JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $this->pdo->exec($sql);

        // Create plugin_options table
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS plugin_options (
    id VARCHAR(255) PRIMARY KEY,
    plugin_slug VARCHAR(255) NOT NULL,
    option_key VARCHAR(255) NOT NULL,
    option_value TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_option (plugin_slug, option_key),
    INDEX idx_plugin (plugin_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $this->pdo->exec($sql);
    }

    /**
     * Register a plugin
     */
    public function register(array $metadata, string $path): string
    {
        $id = $metadata['slug'] ?? $this->generateId();
        $slug = $metadata['slug'] ?? $id;

        $sql = <<<SQL
INSERT INTO plugins (id, name, slug, version, path, metadata, status)
VALUES (:id, :name, :slug, :version, :path, :metadata, 'inactive')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    version = VALUES(version),
    path = VALUES(path),
    metadata = VALUES(metadata),
    updatedAt = CURRENT_TIMESTAMP
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':name' => $metadata['name'] ?? 'Unknown Plugin',
            ':slug' => $slug,
            ':version' => $metadata['version'] ?? '1.0.0',
            ':path' => $path,
            ':metadata' => json_encode($metadata),
        ]);

        return $id;
    }

    /**
     * Get all plugins
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM plugins ORDER BY name ASC";
        $stmt = $this->pdo->query($sql);
        $plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($plugins as &$plugin) {
            $plugin['metadata'] = json_decode($plugin['metadata'] ?? '{}', true);
        }

        return $plugins;
    }

    /**
     * Get active plugins
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM plugins WHERE status = 'active' ORDER BY name ASC";
        $stmt = $this->pdo->query($sql);
        $plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($plugins as &$plugin) {
            $plugin['metadata'] = json_decode($plugin['metadata'] ?? '{}', true);
        }

        return $plugins;
    }

    /**
     * Get plugin by slug
     */
    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM plugins WHERE slug = :slug";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $plugin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($plugin) {
            $plugin['metadata'] = json_decode($plugin['metadata'] ?? '{}', true);
        }

        return $plugin ?: null;
    }

    /**
     * Activate a plugin
     */
    public function activate(string $slug): bool
    {
        $sql = "UPDATE plugins SET status = 'active', updatedAt = CURRENT_TIMESTAMP WHERE slug = :slug";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':slug' => $slug]);
    }

    /**
     * Deactivate a plugin
     */
    public function deactivate(string $slug): bool
    {
        $sql = "UPDATE plugins SET status = 'inactive', updatedAt = CURRENT_TIMESTAMP WHERE slug = :slug";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':slug' => $slug]);
    }

    /**
     * Check if plugin is active
     */
    public function isActive(string $slug): bool
    {
        $plugin = $this->getBySlug($slug);
        return $plugin && $plugin['status'] === 'active';
    }

    /**
     * Delete a plugin
     */
    public function delete(string $slug): bool
    {
        // Delete plugin options first
        $sql = "DELETE FROM plugin_options WHERE plugin_slug = :slug";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':slug' => $slug]);

        // Delete plugin
        $sql = "DELETE FROM plugins WHERE slug = :slug";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':slug' => $slug]);
    }

    /**
     * Get plugin option
     */
    public function getOption(string $pluginSlug, string $key, $default = null)
    {
        $sql = "SELECT option_value FROM plugin_options WHERE plugin_slug = :slug AND option_key = :key";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':slug' => $pluginSlug, ':key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $value = json_decode($result['option_value'], true);
            return $value !== null ? $value : $result['option_value'];
        }

        return $default;
    }

    /**
     * Update plugin option
     */
    public function updateOption(string $pluginSlug, string $key, $value): bool
    {
        $id = $pluginSlug . '_' . $key;
        $jsonValue = is_scalar($value) ? $value : json_encode($value);

        $sql = <<<SQL
INSERT INTO plugin_options (id, plugin_slug, option_key, option_value)
VALUES (:id, :slug, :key, :value)
ON DUPLICATE KEY UPDATE
    option_value = VALUES(option_value),
    updatedAt = CURRENT_TIMESTAMP
SQL;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':slug' => $pluginSlug,
            ':key' => $key,
            ':value' => $jsonValue,
        ]);
    }

    /**
     * Delete plugin option
     */
    public function deleteOption(string $pluginSlug, string $key): bool
    {
        $sql = "DELETE FROM plugin_options WHERE plugin_slug = :slug AND option_key = :key";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':slug' => $pluginSlug, ':key' => $key]);
    }

    /**
     * Generate unique ID
     */
    private function generateId(): string
    {
        return 'plugin_' . bin2hex(random_bytes(8));
    }
}

