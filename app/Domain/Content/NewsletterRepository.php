<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class NewsletterRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM newsletter_subscribers ORDER BY subscribedAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function active(): array
    {
        $statement = $this->pdo->query('SELECT * FROM newsletter_subscribers WHERE status = "ACTIVE" ORDER BY subscribedAt DESC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM newsletter_subscribers WHERE email = :email LIMIT 1');
        $statement->execute([':email' => $email]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function subscribe(string $email, ?string $name = null, ?string $source = null): array
    {
        $existing = $this->findByEmail($email);

        if ($existing) {
            // Update existing subscriber to ACTIVE
            if ($existing['status'] !== 'ACTIVE') {
                $statement = $this->pdo->prepare('UPDATE newsletter_subscribers SET status = "ACTIVE", subscribedAt = NOW(), unsubscribedAt = NULL, source = :source WHERE email = :email');
                $statement->execute([':email' => $email, ':source' => $source]);
            }
            return $this->findByEmail($email);
        }

        $id = Id::prefixed('news');
        $sql = 'INSERT INTO newsletter_subscribers (id, email, name, status, subscribedAt, source) VALUES (:id, :email, :name, "ACTIVE", NOW(), :source)';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':email' => $email,
            ':name' => $name,
            ':source' => $source,
        ]);

        return $this->findByEmail($email);
    }

    public function unsubscribe(string $email): void
    {
        $statement = $this->pdo->prepare('UPDATE newsletter_subscribers SET status = "UNSUBSCRIBED", unsubscribedAt = NOW() WHERE email = :email');
        $statement->execute([':email' => $email]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM newsletter_subscribers WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM newsletter_subscribers WHERE status = "ACTIVE"')->fetchColumn();
    }
}

