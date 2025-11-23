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
        try {
            return self::repository()->get($key, $default);
        } catch (\PDOException $e) {
            // If table doesn't exist, return default value
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Base table or view not found") !== false) {
                error_log("Site options table missing. Please import sql/site_options.sql: " . $e->getMessage());
                return $default;
            }
            // Re-throw other database errors
            throw $e;
        }
    }

    public static function all(): array
    {
        try {
            return self::repository()->all();
        } catch (\PDOException $e) {
            // If table doesn't exist, return empty array
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Base table or view not found") !== false) {
                error_log("Site options table missing. Please import sql/site_options.sql: " . $e->getMessage());
                return [];
            }
            // Re-throw other database errors
            throw $e;
        }
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

