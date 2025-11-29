<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class CeoMessageRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find(): ?array
    {
        $statement = $this->pdo->query('SELECT * FROM ceo_message ORDER BY displayOrder DESC, updatedAt DESC LIMIT 1');
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function published(): ?array
    {
        $statement = $this->pdo->query('SELECT * FROM ceo_message WHERE status = "PUBLISHED" ORDER BY displayOrder DESC, updatedAt DESC LIMIT 1');
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM ceo_message ORDER BY displayOrder DESC, updatedAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM ceo_message WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('ceo');

        $sql = <<<SQL
INSERT INTO ceo_message (
    id, title, message, photo, name, position, signature, displayOrder, status, createdAt, updatedAt
) VALUES (
    :id, :title, :message, :photo, :name, :position, :signature, :displayOrder, :status, NOW(), NOW()
)
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id'          => $data['id'],
            ':title'       => $data['title'],
            ':message'     => $data['message'],
            ':photo'       => $data['photo'],
            ':name'        => $data['name'],
            ':position'    => $data['position'],
            ':signature'   => $data['signature'],
            ':displayOrder' => $data['displayOrder'],
            ':status'      => $data['status'],
        ]);

        return $this->findById($data['id']) ?? $data;
    }

    public function update(string $id, array $attributes): array
    {
        $existing = $this->findById($id);
        if (!$existing) {
            throw new RuntimeException('CEO message not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));

        $sql = <<<SQL
UPDATE ceo_message SET
    title = :title,
    message = :message,
    photo = :photo,
    name = :name,
    position = :position,
    signature = :signature,
    displayOrder = :displayOrder,
    status = :status,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id'          => $id,
            ':title'       => $data['title'],
            ':message'     => $data['message'],
            ':photo'       => $data['photo'],
            ':name'        => $data['name'],
            ':position'    => $data['position'],
            ':signature'   => $data['signature'],
            ':displayOrder' => $data['displayOrder'],
            ':status'      => $data['status'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function delete(string $id): void
    {
        $existing = $this->findById($id);
        if (!$existing) {
            throw new RuntimeException('CEO message not found.');
        }
        
        $statement = $this->pdo->prepare('DELETE FROM ceo_message WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        return [
            'title'        => $attributes['title'] ?? 'Message from CEO',
            'message'      => $attributes['message'] ?? '',
            'photo'        => $attributes['photo'] ?? null,
            'name'         => $attributes['name'] ?? '',
            'position'     => $attributes['position'] ?? $attributes['title'] ?? 'Chief Executive Officer',
            'signature'    => $attributes['signature'] ?? null,
            'displayOrder' => (int) ($attributes['displayOrder'] ?? 0),
            'status'       => $attributes['status'] ?? 'DRAFT',
        ];
    }
}

