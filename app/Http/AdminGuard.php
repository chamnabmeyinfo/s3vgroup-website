<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\Authenticate;

/**
 * @deprecated Use App\Http\Middleware\Authenticate instead
 * Kept for backward compatibility
 */
final class AdminGuard
{
    public static function requireAuth(): void
    {
        Authenticate::requireAuth();
    }
}

