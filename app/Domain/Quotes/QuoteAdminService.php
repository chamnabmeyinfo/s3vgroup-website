<?php

declare(strict_types=1);

namespace App\Domain\Quotes;

use InvalidArgumentException;

final class QuoteAdminService
{
    public function __construct(private readonly QuoteRequestRepository $repository)
    {
    }

    public function list(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        return $this->repository->paginate($filters, $limit, $offset);
    }

    public function updateStatus(string $id, string $status): void
    {
        $status = strtoupper($status);

        if (!in_array($status, ['NEW', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'], true)) {
            throw new InvalidArgumentException('Invalid status value.');
        }

        $this->repository->updateStatus($id, $status);
    }

    public function update(string $id, array $attributes): ?array
    {
        if (isset($attributes['status'])) {
            $attributes['status'] = strtoupper($attributes['status']);
        }

        return $this->repository->update($id, $attributes);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }
}

