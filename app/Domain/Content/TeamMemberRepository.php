<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Support\Id;
use PDO;
use RuntimeException;

final class TeamMemberRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM team_members ORDER BY priority DESC, name ASC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function active(): array
    {
        $statement = $this->pdo->query('SELECT * FROM team_members WHERE status = "ACTIVE" ORDER BY priority DESC, name ASC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(string $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM team_members WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(array $attributes): array
    {
        $data = $this->normalize($attributes);
        $data['id'] = $data['id'] ?? Id::prefixed('team');

        $sql = <<<SQL
INSERT INTO team_members (
    id, name, title, department, bio, expertise, photo, email, phone, location, languages,
    linkedin, twitter, facebook, instagram, website, github, youtube, telegram, whatsapp,
    priority, status, createdAt, updatedAt
) VALUES (
    :id, :name, :title, :department, :bio, :expertise, :photo, :email, :phone, :location, :languages,
    :linkedin, :twitter, :facebook, :instagram, :website, :github, :youtube, :telegram, :whatsapp,
    :priority, :status, NOW(), NOW()
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
            throw new RuntimeException('Team member not found.');
        }

        $data = array_merge($existing, $this->normalize($attributes));

        $sql = <<<SQL
UPDATE team_members SET
    name = :name,
    title = :title,
    department = :department,
    bio = :bio,
    expertise = :expertise,
    photo = :photo,
    email = :email,
    phone = :phone,
    location = :location,
    languages = :languages,
    linkedin = :linkedin,
    twitter = :twitter,
    facebook = :facebook,
    instagram = :instagram,
    website = :website,
    github = :github,
    youtube = :youtube,
    telegram = :telegram,
    whatsapp = :whatsapp,
    priority = :priority,
    status = :status,
    updatedAt = NOW()
WHERE id = :id
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':title' => $data['title'],
            ':department' => $data['department'],
            ':bio' => $data['bio'],
            ':expertise' => $data['expertise'],
            ':photo' => $data['photo'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':location' => $data['location'],
            ':languages' => $data['languages'],
            ':linkedin' => $data['linkedin'],
            ':twitter' => $data['twitter'],
            ':facebook' => $data['facebook'],
            ':instagram' => $data['instagram'],
            ':website' => $data['website'],
            ':github' => $data['github'],
            ':youtube' => $data['youtube'],
            ':telegram' => $data['telegram'],
            ':whatsapp' => $data['whatsapp'],
            ':priority' => (int) $data['priority'],
            ':status' => $data['status'],
        ]);

        return $this->findById($id) ?? $data;
    }

    public function delete(string $id): void
    {
        $existing = $this->findById($id);
        if (!$existing) {
            throw new RuntimeException('Team member not found.');
        }
        
        $statement = $this->pdo->prepare('DELETE FROM team_members WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    private function normalize(array $attributes): array
    {
        return [
            'name'       => $attributes['name'] ?? '',
            'title'      => $attributes['title'] ?? '',
            'department' => $attributes['department'] ?? null,
            'bio'        => $attributes['bio'] ?? null,
            'expertise'  => $attributes['expertise'] ?? null,
            'photo'      => $attributes['photo'] ?? null,
            'email'      => $attributes['email'] ?? null,
            'phone'      => $attributes['phone'] ?? null,
            'location'   => $attributes['location'] ?? null,
            'languages'  => $attributes['languages'] ?? null,
            'linkedin'   => $attributes['linkedin'] ?? null,
            'twitter'    => $attributes['twitter'] ?? null,
            'facebook'   => $attributes['facebook'] ?? null,
            'instagram'  => $attributes['instagram'] ?? null,
            'website'    => $attributes['website'] ?? null,
            'github'     => $attributes['github'] ?? null,
            'youtube'    => $attributes['youtube'] ?? null,
            'telegram'   => $attributes['telegram'] ?? null,
            'whatsapp'   => $attributes['whatsapp'] ?? null,
            'priority'   => (int) ($attributes['priority'] ?? 0),
            'status'     => $attributes['status'] ?? 'ACTIVE',
        ];
    }
}

