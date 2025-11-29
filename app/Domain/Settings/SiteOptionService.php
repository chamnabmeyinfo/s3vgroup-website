<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Domain\Exceptions\DomainException;
use App\Domain\Exceptions\NotFoundException;

final class SiteOptionService
{
    private const VALID_GROUPS = [
        'general', 'design', 'contact', 'social', 'homepage', 'footer', 'advanced',
    ];

    private const VALID_TYPES = [
        'text', 'textarea', 'number', 'boolean', 'json', 'color', 'image', 'url',
    ];

    /** @var SiteOptionRepository */
    private $repository;

    public function __construct(SiteOptionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function getGrouped(): array
    {
        $all = $this->repository->all();
        $grouped = [];

        foreach ($all as $option) {
            $group = $option['group_name'] ?? 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $option;
        }

        return $grouped;
    }

    public function getByGroup(string $group): array
    {
        return $this->repository->byGroup($group);
    }

    public function get(string $key, $default = null)
    {
        return $this->repository->get($key, $default);
    }

    public function findById(string $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function update(string $id, array $payload): array
    {
        $data = $this->validate($payload, true);

        return $this->repository->update($id, $data);
    }

    public function bulkUpdate(array $options): void
    {
        $this->repository->bulkUpdate($options);
    }

    private function validate(array $payload, bool $partial = false): array
    {
        if (isset($payload['type']) && !in_array($payload['type'], self::VALID_TYPES, true)) {
            throw new DomainException('Invalid option type. Must be one of: ' . implode(', ', self::VALID_TYPES));
        }

        if (isset($payload['group_name']) && !in_array($payload['group_name'], self::VALID_GROUPS, true)) {
            throw new DomainException('Invalid option group. Must be one of: ' . implode(', ', self::VALID_GROUPS));
        }

        return [
            'key_name'   => $payload['key_name'] ?? null,
            'value'      => $payload['value'] ?? null,
            'type'       => $payload['type'] ?? null,
            'group_name' => $payload['group_name'] ?? null,
            'label'      => $payload['label'] ?? null,
            'description'=> $payload['description'] ?? null,
            'priority'   => isset($payload['priority']) ? (int) $payload['priority'] : null,
        ];
    }
}

