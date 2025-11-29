<?php

declare(strict_types=1);

namespace App\Domain\Quotes;

use InvalidArgumentException;

final class QuoteService
{
    /** @var QuoteRequestRepository */
    private $repository;

    public function __construct(QuoteRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function submit(array $payload): array
    {
        $clean = $this->validate($payload);

        return $this->repository->create($clean);
    }

    private function validate(array $payload): array
    {
        $required = ['companyName', 'contactName', 'email'];

        foreach ($required as $field) {
            if (empty($payload[$field]) || !is_string($payload[$field])) {
                throw new InvalidArgumentException(sprintf('%s is required.', $field));
            }
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        $clean = [
            'companyName' => trim($payload['companyName']),
            'contactName' => trim($payload['contactName']),
            'email'       => strtolower(trim($payload['email'])),
            'phone'       => isset($payload['phone']) ? trim((string) $payload['phone']) : null,
            'message'     => isset($payload['message']) ? trim((string) $payload['message']) : null,
            'source'      => $payload['source'] ?? 'website',
        ];

        if (isset($payload['items'])) {
            $clean['items'] = $this->normalizeItems($payload['items']);
        }

        return $clean;
    }

    private function normalizeItems($items): array
    {
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($items)) {
            throw new InvalidArgumentException('Items must be an array or JSON string.');
        }

        return array_values(array_map(static function ($item) {
            if (!is_array($item)) {
                return [];
            }

            return [
                'id'       => $item['id'] ?? null,
                'name'     => $item['name'] ?? null,
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 1,
                'notes'    => $item['notes'] ?? null,
            ];
        }, $items));
    }
}

