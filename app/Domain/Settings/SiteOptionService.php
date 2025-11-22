<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use InvalidArgumentException;

final class SiteOptionService
{
    private const VALID_GROUPS = [
        'general', 'design', 'contact', 'social', 'homepage', 'footer',
    ];

    private const VALID_TYPES = [
        'text', 'textarea', 'number', 'boolean', 'json', 'color', 'image', 'url',
    ];

    public function __construct(private readonly SiteOptionRepository $repository)
    {
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

    public function get(string $key, $default = null)
    {
        return $this->repository->get($key, $default);
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
            throw new InvalidArgumentException('Invalid option type.');
        }

        if (isset($payload['group_name']) && !in_array($payload['group_name'], self::VALID_GROUPS, true)) {
            throw new InvalidArgumentException('Invalid option group.');
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

