<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Settings\SiteOptionRepository;
use App\Database\Connection;

final class SiteOptionHelper
{
    private static ?SiteOptionRepository $repository = null;

    private static function repository(): SiteOptionRepository
    {
        if (self::$repository === null) {
            self::$repository = new SiteOptionRepository(Connection::getInstance());
        }

        return self::$repository;
    }

    public static function get(string $key, $default = null)
    {
        return self::repository()->get($key, $default);
    }

    public static function all(): array
    {
        return self::repository()->all();
    }

    public static function grouped(): array
    {
        $all = self::repository()->all();
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
}

