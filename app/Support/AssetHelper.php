<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Asset Helper - Ensures assets load correctly on both localhost and live server
 */
final class AssetHelper
{
    private static ?string $basePath = null;

    /**
     * Get the base path for the website
     * Detects if we're in a subdirectory (localhost) or root (live)
     */
    public static function basePath(): string
    {
        if (self::$basePath !== null) {
            return self::$basePath;
        }

        // Method 1: Use SCRIPT_NAME to detect subdirectory
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $scriptDir = dirname($scriptName);
        
        // If script is in a subdirectory (e.g., /s3vgroup/index.php)
        if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '') {
            self::$basePath = rtrim($scriptDir, '/');
            return self::$basePath;
        }

        // Method 2: Check REQUEST_URI for subdirectory pattern
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUri = parse_url($requestUri, PHP_URL_PATH);
        
        if ($parsedUri && $parsedUri !== '/') {
            // Extract first segment (potential subdirectory)
            $segments = explode('/', trim($parsedUri, '/'));
            if (!empty($segments[0]) && $segments[0] !== 'index.php' && $segments[0] !== 'admin') {
                // Check if this segment exists as a directory
                $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
                if ($documentRoot && is_dir($documentRoot . '/' . $segments[0])) {
                    self::$basePath = '/' . $segments[0];
                    return self::$basePath;
                }
            }
        }

        // Method 3: Check if index.php is in a subdirectory
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? '';
        
        if ($documentRoot && $scriptFile) {
            $relativePath = str_replace($documentRoot, '', dirname($scriptFile));
            $relativePath = str_replace('\\', '/', $relativePath);
            $relativePath = trim($relativePath, '/');
            
            if ($relativePath && $relativePath !== '') {
                self::$basePath = '/' . $relativePath;
                return self::$basePath;
            }
        }

        // Default: root directory (no subdirectory)
        self::$basePath = '';
        return self::$basePath;
    }

    /**
     * Get asset URL (CSS, JS, images)
     * Works on both localhost subdirectory and live root
     */
    public static function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $base = self::basePath();
        
        return $base . '/' . $path;
    }

    /**
     * Get URL for any path
     */
    public static function url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        $base = self::basePath();
        
        if ($path === '') {
            return $base ?: '/';
        }
        
        return $base . '/' . $path;
    }
}

