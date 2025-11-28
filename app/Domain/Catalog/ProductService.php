<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Support\Str;
use InvalidArgumentException;

final class ProductService
{
    public function __construct(private readonly ProductRepository $repository)
    {
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

    public function updateStatus(string $id, string $status): void
    {
        $status = strtoupper($status);

        if (!in_array($status, ['DRAFT', 'PUBLISHED', 'ARCHIVED'], true)) {
            throw new InvalidArgumentException('Invalid status value.');
        }

        $this->repository->updateStatus($id, $status);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }

    public function findById(string $id): ?array
    {
        return $this->repository->findById($id);
    }

    private function validate(array $payload, bool $partial = false): array
    {
        if (!$partial) {
            $this->assertRequired($payload, ['name', 'categoryId']);
        }

        if (isset($payload['email'])) {
            unset($payload['email']);
        }

        $name = $payload['name'] ?? null;
        $slug = $payload['slug'] ?? null;

        if ($slug === null && $name !== null) {
            $slug = Str::slug($name);
        }

        if (isset($payload['status']) && !in_array($payload['status'], ['DRAFT', 'PUBLISHED', 'ARCHIVED'], true)) {
            throw new InvalidArgumentException('Invalid status value.');
        }

        return [
            'name'        => $name,
            'slug'        => $slug,
            'sku'         => $payload['sku'] ?? null,
            'summary'     => $payload['summary'] ?? null,
            'description' => $payload['description'] ?? null,
            'specs'       => $payload['specs'] ?? null,
            'heroImage'   => $payload['heroImage'] ?? null,
            'price'       => isset($payload['price']) ? (float) $payload['price'] : null,
            'status'      => $payload['status'] ?? 'DRAFT',
            'highlights'  => $payload['highlights'] ?? null,
            'categoryId'  => $payload['categoryId'] ?? null,
        ];
    }

    private function assertRequired(array $payload, array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                throw new InvalidArgumentException(sprintf('%s is required.', $field));
            }
        }
    }
}

