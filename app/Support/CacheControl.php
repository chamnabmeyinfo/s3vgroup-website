<?php

declare(strict_types=1);

namespace App\Support;

use Throwable;

/**
 * Handles cache headers based on environment variables and site options.
 */
final class CacheControl
{
    private static bool $applied = false;

    /**
     * Apply cache headers (idempotent).
     */
    public static function apply(): void
    {
        if (self::$applied || PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        $mode = strtolower((string) env('CACHE_MODE', 'auto'));
        $fallbackTtl = max(60, (int) env('CACHE_TTL', 3600));

        if (in_array($mode, ['off', 'disable', 'disabled'], true)) {
            self::disable('env');
            return;
        }

        if (in_array($mode, ['on', 'enable', 'enabled'], true)) {
            self::enable($fallbackTtl);
            return;
        }

        $appEnv = strtolower((string) env('APP_ENV', 'production'));
        if (in_array($appEnv, ['local', 'development', 'dev', 'testing'], true)) {
            self::disable('app_env');
            return;
        }

        $optionEnabled = false;
        $ttl = $fallbackTtl;

        try {
            $optionEnabled = SiteOptionHelper::get('enable_caching', '0') === '1';
            $ttl = max(60, (int) SiteOptionHelper::get('cache_duration', $fallbackTtl));
        } catch (Throwable $e) {
            // If options table is unavailable, stay in dev-friendly mode (cache disabled)
            $optionEnabled = false;
        }

        if (!$optionEnabled) {
            self::disable('option');
            return;
        }

        self::enable($ttl);
    }

    private static function disable(string $reason): void
    {
        if (headers_sent()) {
            return;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Cache-Mode: disabled-' . $reason);
        self::$applied = true;
    }

    private static function enable(int $ttl): void
    {
        if (headers_sent()) {
            return;
        }

        header('Cache-Control: public, max-age=' . $ttl . ', s-maxage=' . $ttl);
        header('Pragma: cache');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT');
        header('X-Cache-Mode: enabled');
        self::$applied = true;
    }
}


