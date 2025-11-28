<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Exceptions\UnauthorizedException;

/**
 * Authentication middleware
 * 
 * Ensures user is authenticated before accessing protected routes
 */
final class Authenticate
{
    /**
     * Require authentication
     * 
     * @throws UnauthorizedException
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            throw new UnauthorizedException('Authentication required. Please log in.');
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Get authenticated user ID
     */
    public static function userId(): ?string
    {
        if (!self::check()) {
            return null;
        }

        return $_SESSION['admin_user_id'] ?? null;
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_user_id']);
        
        session_destroy();
    }
}

