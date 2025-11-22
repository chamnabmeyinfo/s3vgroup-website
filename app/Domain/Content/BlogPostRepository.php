<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use App\Support\Str;
use PDO;
use RuntimeException;

final class BlogPostRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM blog_posts ORDER BY createdAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function published(int $limit = null, int $offset = 0): array
    {
        $sql = 'SELECT * FROM blog_posts WHERE status = "PUBLISHED" ORDER BY publishedAt DESC, createdAt DESC';
        
        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }

        $statement = $this->pdo->prepare($sql);
        
        if ($limit !== null) {
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM blog_posts WHERE slug = :slug LIMIT 1');
        $statement->execute([':slug' => $slug]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['tags']) {
            $result['tags'] = json_decode($result['tags'], true) ?? [];
        }
        
        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('post');
        
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $sql = <<<SQL
INSERT INTO blog_posts (
    id, title, slug, excerpt, content, featured_image, author_name, author_email,
    category, tags, views, status, publishedAt, createdAt, updatedAt
) VALUES (
    :id, :title, :slug, :excerpt, :content, :featured_image, :author_name, :author_email,
    :category, :tags, :views, :status, :publishedAt, NOW(), NOW()
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
            throw new RuntimeException('Blog post not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));
        
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $sql = <<<SQL
UPDATE blog_posts SET
    title = :title,
    slug = :slug,
    excerpt = :excerpt,
    content = :content,
    featured_image = :featured_image,
    author_name = :author_name,
    author_email = :author_email,
    category = :category,
    tags = :tags,
    status = :status,
    publishedAt = :publishedAt,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':featured_image' => $data['featured_image'],
            ':author_name' => $data['author_name'],
            ':author_email' => $data['author_email'],
            ':category' => $data['category'],
            ':tags' => $data['tags'],
            ':status' => $data['status'],
            ':publishedAt' => $data['publishedAt'] ?? ($data['status'] === 'PUBLISHED' ? date('Y-m-d H:i:s') : null),
        ]);

        return $this->findById($id) ?? $data;
    }

    public function incrementViews(string $id): void
    {
        $statement = $this->pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM blog_posts WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        $tags = $attributes['tags'] ?? [];
        if (is_string($tags)) {
            $tags = array_filter(array_map('trim', explode(',', $tags)));
        }

        return [
            'title'         => $attributes['title'] ?? '',
            'slug'          => $attributes['slug'] ?? null,
            'excerpt'       => $attributes['excerpt'] ?? null,
            'content'       => $attributes['content'] ?? '',
            'featured_image'=> $attributes['featured_image'] ?? null,
            'author_name'   => $attributes['author_name'] ?? null,
            'author_email'  => $attributes['author_email'] ?? null,
            'category'      => $attributes['category'] ?? null,
            'tags'          => json_encode($tags, JSON_UNESCAPED_UNICODE),
            'views'         => (int) ($attributes['views'] ?? 0),
            'status'        => $attributes['status'] ?? 'DRAFT',
            'publishedAt'   => $attributes['publishedAt'] ?? null,
        ];
    }
}

