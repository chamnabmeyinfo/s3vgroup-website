<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class PageRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM pages ORDER BY priority DESC, title ASC');
        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function published(): array
    {
        $statement = $this->pdo->query('SELECT * FROM pages WHERE status = "PUBLISHED" ORDER BY priority DESC, title ASC');
        return array_map([$this, 'transform'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM pages WHERE slug = :slug AND status = "PUBLISHED" LIMIT 1');
        $statement->execute([':slug' => $slug]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->transform($result) : null;
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM pages WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->transform($result) : null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('page');
        
        // Ensure slug is unique
        $data['slug'] = $this->ensureUniqueSlug($data['slug']);
        
        // Handle homepage designation
        $settings = json_decode($data['settings'], true) ?? [];
        if (isset($settings['is_homepage']) && $settings['is_homepage']) {
            // Unset homepage from all other pages
            $this->unsetHomepageFromOthers($data['id']);
        }

        $sql = <<<SQL
INSERT INTO pages (
    id, title, slug, description, page_type, status, template, meta_title, meta_description, meta_keywords, 
    featured_image, settings, priority, parent_id, createdAt, updatedAt
) VALUES (
    :id, :title, :slug, :description, :page_type, :status, :template, :meta_title, :meta_description, :meta_keywords,
    :featured_image, :settings, :priority, :parent_id, NOW(), NOW()
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
            throw new RuntimeException('Page not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));
        
        // Ensure slug is unique (excluding current page)
        if ($data['slug'] !== $existing['slug']) {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $id);
        }
        
        // Handle homepage designation
        $settings = json_decode($data['settings'], true) ?? [];
        if (isset($settings['is_homepage']) && $settings['is_homepage']) {
            // Unset homepage from all other pages
            $this->unsetHomepageFromOthers($id);
        }

        $sql = <<<SQL
    UPDATE pages SET
        title = :title,
        slug = :slug,
        description = :description,
        page_type = :page_type,
        status = :status,
        template = :template,
        meta_title = :meta_title,
        meta_description = :meta_description,
        meta_keywords = :meta_keywords,
        featured_image = :featured_image,
        settings = :settings,
        priority = :priority,
        parent_id = :parent_id,
        updatedAt = NOW()
    WHERE id = :id
    SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':description' => $data['description'],
            ':page_type' => $data['page_type'],
            ':status' => $data['status'],
            ':template' => $data['template'],
            ':meta_title' => $data['meta_title'],
            ':meta_description' => $data['meta_description'],
            ':meta_keywords' => $data['meta_keywords'],
            ':featured_image' => $data['featured_image'],
            ':settings' => $data['settings'],
            ':priority' => (int) $data['priority'],
            ':parent_id' => $data['parent_id'],
        ]);

        return $this->findById($id) ?? $data;
    }
    
    /**
     * Unset homepage designation from all pages except the specified one
     */
    private function unsetHomepageFromOthers(string $exceptId): void
    {
        // Get all pages except the current one
        $statement = $this->pdo->prepare('SELECT id, settings FROM pages WHERE id != :id');
        $statement->execute([':id' => $exceptId]);
        $pages = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($pages as $page) {
            $settings = json_decode($page['settings'] ?? '{}', true);
            if (isset($settings['is_homepage']) && $settings['is_homepage']) {
                // Unset homepage flag
                $settings['is_homepage'] = false;
                $updateStmt = $this->pdo->prepare('UPDATE pages SET settings = :settings WHERE id = :id');
                $updateStmt->execute([
                    ':id' => $page['id'],
                    ':settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                ]);
            }
        }
    }
    
    /**
     * Find the homepage (page with is_homepage setting)
     */
    public function findHomepage(): ?array
    {
        $statement = $this->pdo->query('SELECT * FROM pages WHERE status = "PUBLISHED" ORDER BY priority DESC, createdAt ASC');
        $pages = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($pages as $page) {
            $settings = json_decode($page['settings'] ?? '{}', true);
            if (isset($settings['is_homepage']) && $settings['is_homepage']) {
                return $this->transformLocalized($page);
            }
        }
        
        return null;
    }

    public function delete(string $id): void
    {
        $existing = $this->findById($id);
        if (!$existing) {
            throw new RuntimeException('Page not found.');
        }
        
        $statement = $this->pdo->prepare('DELETE FROM pages WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        $title = trim($attributes['title'] ?? '');
        $slug = $this->sanitizeSlug($attributes['slug'] ?? '', $title);
        
        return [
            'title' => $title,
            'slug' => $slug,
            'description' => isset($attributes['description']) ? trim((string) $attributes['description']) : null,
            'page_type' => $attributes['page_type'] ?? 'page',
            'status' => $attributes['status'] ?? 'DRAFT',
            'template' => isset($attributes['template']) ? trim((string) $attributes['template']) : null,
            'meta_title' => isset($attributes['meta_title']) ? trim((string) $attributes['meta_title']) : null,
            'meta_description' => isset($attributes['meta_description']) ? trim((string) $attributes['meta_description']) : null,
            'meta_keywords' => isset($attributes['meta_keywords']) ? trim((string) $attributes['meta_keywords']) : null,
            'featured_image' => isset($attributes['featured_image']) ? trim((string) $attributes['featured_image']) : null,
            'settings' => json_encode($attributes['settings'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'priority' => (int) ($attributes['priority'] ?? 0),
            'parent_id' => isset($attributes['parent_id']) ? trim((string) $attributes['parent_id']) : null,
        ];
    }
    
    private function sanitizeSlug(?string $slug, ?string $title): string
    {
        // If slug is empty, generate from title
        if (empty($slug)) {
            if (empty($title)) {
                return 'page-' . bin2hex(random_bytes(4));
            }
            $slug = $title;
        }
        
        // Trim all whitespace (including tabs, newlines, etc.)
        $slug = trim($slug);
        
        // Convert to lowercase
        $slug = strtolower($slug);
        
        // Replace any non-alphanumeric characters (except hyphens) with hyphens
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
        
        // If slug is empty after sanitization, generate a fallback
        if (empty($slug)) {
            $slug = 'page-' . bin2hex(random_bytes(4));
        }
        
        return $slug;
    }
    
    private function ensureUniqueSlug(string $slug, ?string $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $statement = $this->pdo->prepare('SELECT COUNT(*) FROM pages WHERE slug = :slug' . ($excludeId ? ' AND id != :id' : ''));
            $params = [':slug' => $slug];
            if ($excludeId) {
                $params[':id'] = $excludeId;
            }
            $statement->execute($params);
            $count = (int) $statement->fetchColumn();
            
            if ($count === 0) {
                return $slug;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            // Safety check to prevent infinite loop
            if ($counter > 1000) {
                $slug = $originalSlug . '-' . bin2hex(random_bytes(4));
                break;
            }
        }
        
        return $slug;
    }

    private function transform(array $page): array
    {
        $page['settings'] = json_decode($page['settings'] ?? '[]', true);
        return $page;
    }

}

