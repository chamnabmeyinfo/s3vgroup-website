<?php

declare(strict_types=1);

namespace App\Domain\Quotes;

use App\Support\Id;
use PDO;

final class QuoteRequestRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(array $attributes): array
    {
        $data = [
            'id'          => Id::prefixed('quote'),
            'companyName' => $attributes['companyName'],
            'contactName' => $attributes['contactName'],
            'email'       => $attributes['email'],
            'phone'       => $attributes['phone'] ?? null,
            'message'     => $attributes['message'] ?? null,
            'items'       => $this->encodeItems($attributes['items'] ?? null),
            'status'      => $attributes['status'] ?? 'NEW',
            'source'      => $attributes['source'] ?? null,
        ];

        $sql = <<<SQL
INSERT INTO quote_requests (id, companyName, contactName, email, phone, message, items, status, source, createdAt, updatedAt)
VALUES (:id, :companyName, :contactName, :email, :phone, :message, :items, :status, :source, NOW(), NOW())
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute($data);

        return $this->findById($data['id']) ?? $data;
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM quote_requests WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $quote = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$quote) {
            return null;
        }

        $quote['items'] = $this->decodeItems($quote['items']);

        return $quote;
    }

    public function paginate(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        $conditions = [];
        $params = [
            ':limit'  => $limit,
            ':offset' => $offset,
        ];

        if (!empty($filters['status'])) {
            $conditions[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['email'])) {
            $conditions[] = 'email = :email';
            $params[':email'] = $filters['email'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = <<<SQL
SELECT * FROM quote_requests
$where
ORDER BY createdAt DESC
LIMIT :limit OFFSET :offset
SQL;

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $type = in_array($key, [':limit', ':offset'], true) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['items'] = $this->decodeItems($row['items']);
        }

        return $rows;
    }

    public function updateStatus(string $id, string $status): void
    {
        $statement = $this->pdo->prepare('UPDATE quote_requests SET status = :status, updatedAt = NOW() WHERE id = :id');
        $statement->execute([
            ':id'     => $id,
            ':status' => $status,
        ]);
    }

    public function update(string $id, array $attributes): ?array
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['companyName', 'contactName', 'email', 'phone', 'message', 'source'] as $field) {
            if (array_key_exists($field, $attributes)) {
                $fields[] = sprintf('%s = :%s', $field, $field);
                $params[':' . $field] = $attributes[$field];
            }
        }

        if (array_key_exists('items', $attributes)) {
            $fields[] = 'items = :items';
            $params[':items'] = $this->encodeItems($attributes['items']);
        }

        if (array_key_exists('status', $attributes)) {
            $fields[] = 'status = :status';
            $params[':status'] = $attributes['status'];
        }

        if (!$fields) {
            return $this->findById($id);
        }

        $fields[] = 'updatedAt = NOW()';

        $sql = sprintf(
            'UPDATE quote_requests SET %s WHERE id = :id',
            implode(', ', $fields)
        );

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $this->findById($id);
    }

    private function encodeItems($items): ?string
    {
        if ($items === null) {
            return null;
        }

        if (is_string($items)) {
            return $items;
        }

        return json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function decodeItems(?string $items): ?array
    {
        if ($items === null || $items === '') {
            return null;
        }

        $decoded = json_decode($items, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function delete(string $id): void
    {
        $existing = $this->findById($id);

        if (!$existing) {
            throw new RuntimeException('Quote request not found.');
        }

        $statement = $this->pdo->prepare('DELETE FROM quote_requests WHERE id = :id');
        $statement->execute([':id' => $id]);
    }
}

