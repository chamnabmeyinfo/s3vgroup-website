<?php

declare(strict_types=1);

namespace App\Support;

final class Id
{
    public static function prefixed(string $prefix): string
    {
        return sprintf('%s_%s', rtrim($prefix, '_'), self::random());
    }

    public static function random(int $bytes = 8): string
    {
        return bin2hex(random_bytes($bytes));
    }
}

