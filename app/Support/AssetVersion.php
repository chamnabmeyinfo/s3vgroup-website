<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Asset Version Manager
 * Manages version numbers for cache busting (better than time() for caching)
 */
final class AssetVersion
{
    private static ?string $version = null;
    private const VERSION_FILE = 'VERSION';

    /**
     * Get current version number
     * Uses VERSION file or falls back to git commit hash or timestamp
     */
    public static function get(): string
    {
        if (self::$version !== null) {
            return self::$version;
        }

        $versionFile = base_path(self::VERSION_FILE);
        
        // Try to read from VERSION file
        if (file_exists($versionFile)) {
            $version = trim(file_get_contents($versionFile));
            if ($version) {
                self::$version = $version;
                return $version;
            }
        }

        // Try git commit hash (short)
        $gitDir = base_path('.git');
        if (is_dir($gitDir)) {
            $headFile = $gitDir . '/HEAD';
            if (file_exists($headFile)) {
                $head = trim(file_get_contents($headFile));
                if (preg_match('/ref: (.+)/', $head, $matches)) {
                    $refFile = $gitDir . '/' . $matches[1];
                    if (file_exists($refFile)) {
                        $hash = trim(file_get_contents($refFile));
                        if ($hash && strlen($hash) >= 7) {
                            self::$version = substr($hash, 0, 7);
                            return self::$version;
                        }
                    }
                }
            }
        }

        // Fallback to timestamp (but only update daily, not every request)
        $cacheFile = base_path('cache/version.txt');
        if (file_exists($cacheFile)) {
            $cached = file_get_contents($cacheFile);
            $parts = explode('|', $cached);
            if (count($parts) === 2 && (time() - (int)$parts[1]) < 86400) {
                self::$version = $parts[0];
                return self::$version;
            }
        }

        // Generate new version
        $version = date('Ymd');
        if (!is_dir(base_path('cache'))) {
            @mkdir(base_path('cache'), 0755, true);
        }
        @file_put_contents($cacheFile, $version . '|' . time());
        
        self::$version = $version;
        return $version;
    }

    /**
     * Get asset URL with version
     */
    public static function url(string $path): string
    {
        $path = ltrim($path, '/');
        $version = self::get();
        $separator = strpos($path, '?') !== false ? '&' : '?';
        return AssetHelper::asset($path) . $separator . 'v=' . $version;
    }
}

