<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\ProductService as DomainProductService;

/**
 * Application service for products
 * 
 * Orchestrates product operations and coordinates between layers
 */
final class ProductService
{
    private DomainProductService $domainService;

    public function __construct(ProductRepository $repository)
    {
        $this->domainService = new DomainProductService($repository);
    }

    public function findById(string $id): ?array
    {
        return $this->domainService->findById($id);
    }

    public function create(array $payload): array
    {
        return $this->domainService->create($payload);
    }

    public function update(string $id, array $payload): array
    {
        return $this->domainService->update($id, $payload);
    }

    public function delete(string $id): void
    {
        $this->domainService->delete($id);
    }
}

