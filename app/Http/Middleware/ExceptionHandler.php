<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Exceptions\ConflictException;
use App\Domain\Exceptions\DomainException;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\ValidationException;
use App\Http\JsonResponse;
use App\Infrastructure\Logging\Logger;

/**
 * Centralized exception handler
 * 
 * Converts exceptions to consistent JSON error responses
 */
final class ExceptionHandler
{
    public static function handle(\Throwable $exception): void
    {
        // Log the exception
        self::logException($exception);

        // Convert to appropriate HTTP response
        if ($exception instanceof ValidationException) {
            self::handleValidationException($exception);
        } elseif ($exception instanceof NotFoundException) {
            JsonResponse::error(
                $exception->getMessage(),
                404,
                $exception->getErrorCode()
            );
        } elseif ($exception instanceof UnauthorizedException) {
            JsonResponse::error(
                $exception->getMessage(),
                401,
                $exception->getErrorCode()
            );
        } elseif ($exception instanceof ForbiddenException) {
            JsonResponse::error(
                $exception->getMessage(),
                403,
                $exception->getErrorCode()
            );
        } elseif ($exception instanceof ConflictException) {
            JsonResponse::error(
                $exception->getMessage(),
                409,
                $exception->getErrorCode()
            );
        } elseif ($exception instanceof DomainException) {
            JsonResponse::error(
                $exception->getMessage(),
                422,
                $exception->getErrorCode()
            );
        } elseif ($exception instanceof \PDOException) {
            // Database errors - hide details in production
            $message = self::isDebugMode() 
                ? $exception->getMessage() 
                : 'A database error occurred. Please try again later.';
            
            JsonResponse::error(
                $message,
                500,
                'DATABASE_ERROR'
            );
        } else {
            // Unknown errors - hide details in production
            $message = self::isDebugMode() 
                ? $exception->getMessage() 
                : 'An unexpected error occurred. Please try again later.';
            
            JsonResponse::error(
                $message,
                500,
                'INTERNAL_ERROR'
            );
        }
    }

    private static function handleValidationException(ValidationException $exception): void
    {
        $details = [];
        
        if ($exception->hasErrors()) {
            $details['fields'] = $exception->getErrors();
        }

        JsonResponse::error(
            $exception->getMessage(),
            422,
            $exception->getErrorCode(),
            $details
        );
    }

    private static function logException(\Throwable $exception): void
    {
        $context = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => self::isDebugMode() ? $exception->getTraceAsString() : null,
        ];

        // Add additional context for domain exceptions
        if ($exception instanceof ValidationException && $exception->hasErrors()) {
            $context['validation_errors'] = $exception->getErrors();
        }

        Logger::error($exception->getMessage(), $context);
    }

    private static function isDebugMode(): bool
    {
        return defined('AE_DEBUG') && AE_DEBUG === true;
    }
}

