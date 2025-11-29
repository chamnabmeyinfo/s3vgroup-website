<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Support\Str;
use InvalidArgumentException;

final class CategoryService
{
    /** @var CategoryRepository */
    private $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $payload): array
    {
        $data = $this->validate($payload);

        return $this->repository->create($data);
    }

    public function update(string $id, array $payload): array
    {
        $data = $this->validate($payload, true);

        return $this->repository->update($id, $data);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }

    private function validate(array $payload, bool $partial = false): array
    {
        if (!$partial || array_key_exists('name', $payload)) {
            if (empty($payload['name'])) {
                throw new InvalidArgumentException('Category name is required.');
            }
        }

        $name = isset($payload['name']) ? trim((string) $payload['name']) : null;
        $slug = isset($payload['slug']) ? trim((string) $payload['slug']) : null;

        if ($slug === null && $name !== null) {
            $slug = Str::slug($name);
        }

        return [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $payload['description'] ?? null,
            'icon'        => $payload['icon'] ?? null,
            'priority'    => isset($payload['priority']) ? (int) $payload['priority'] : null,
        ];
    }
}

