<?php

declare(strict_types=1);

namespace App\Http;

final class AdminGuard
{
    public static function requireAuth(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            JsonResponse::error('Unauthorized', 401);
        }
    }
}

