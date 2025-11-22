<?php

declare(strict_types=1);

namespace App\Support;

final class Env
{
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded || !file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || self::isComment($line)) {
                continue;
            }

            [$name, $value] = self::parseLine($line);
            self::store($name, $value);
        }

        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }

    private static function isComment(string $line): bool
    {
        return strncmp($line, '#', 1) === 0 || strncmp($line, ';', 1) === 0;
    }

    private static function parseLine(string $line): array
    {
        $parts = explode('=', $line, 2);
        $name = trim($parts[0]);
        $value = $parts[1] ?? '';
        $value = trim($value);

        if (self::isQuoted($value)) {
            $value = substr($value, 1, -1);
        }

        return [$name, $value];
    }

    private static function isQuoted(string $value): bool
    {
        $length = strlen($value);

        return $length >= 2 && $value[0] === '"' && $value[$length - 1] === '"';
    }

    private static function store(string $name, string $value): void
    {
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv(sprintf('%s=%s', $name, $value));
    }
}

