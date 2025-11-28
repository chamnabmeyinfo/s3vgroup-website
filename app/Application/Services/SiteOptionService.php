<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Settings\SiteOptionRepository;
use App\Domain\Settings\SiteOptionService as DomainSiteOptionService;

/**
 * Application service for site options
 * 
 * Orchestrates site option operations
 */
final class SiteOptionService
{
    private DomainSiteOptionService $domainService;

    public function __construct(SiteOptionRepository $repository)
    {
        $this->domainService = new DomainSiteOptionService($repository);
    }

    public function getAll(): array
    {
        return $this->domainService->getAll();
    }

    public function getGrouped(): array
    {
        return $this->domainService->getGrouped();
    }

    public function getByGroup(string $group): array
    {
        return $this->domainService->getByGroup($group);
    }

    public function get(string $key, $default = null)
    {
        return $this->domainService->get($key, $default);
    }

    public function findById(string $id): ?array
    {
        return $this->domainService->findById($id);
    }

    public function update(string $id, array $payload): array
    {
        return $this->domainService->update($id, $payload);
    }

    public function bulkUpdate(array $options): void
    {
        $this->domainService->bulkUpdate($options);
    }
}

