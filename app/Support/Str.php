<?php

declare(strict_types=1);

namespace App\Support;

final class Str
{
    public static function slug(string $value): string
    {
        $slug = strtolower($value);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : self::randomFallback();
    }

    private static function randomFallback(): string
    {
        return bin2hex(random_bytes(4));
    }
}

