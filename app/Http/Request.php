<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function input(string $key, $default = null)
    {
        $body = self::json();

        if (is_array($body) && array_key_exists($key, $body)) {
            return $body[$key];
        }

        return $_POST[$key] ?? $default;
    }

    public static function json(): ?array
    {
        if (!in_array(self::method(), ['POST', 'PUT', 'PATCH'], true)) {
            return null;
        }

        $raw = file_get_contents('php://input');

        if ($raw === false || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    public static function segment(int $index): ?string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);
        
        if (!$path) {
            return null;
        }

        // Remove leading/trailing slashes and split
        $segments = array_filter(explode('/', trim($path, '/')));
        $segments = array_values($segments);

        // Index is 1-based (first segment is index 1)
        $arrayIndex = $index - 1;
        
        return isset($segments[$arrayIndex]) ? $segments[$arrayIndex] : null;
    }
}

