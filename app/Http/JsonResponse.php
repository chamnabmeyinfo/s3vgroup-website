<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Consistent JSON response formatter
 * 
 * All API responses follow this format:
 * {
 *   "data": { ... },
 *   "error": null | { "code": "...", "message": "...", "details": { ... } },
 *   "meta": { "timestamp": "..." }
 * }
 */
final class JsonResponse
{
    /**
     * Send success response
     */
    public static function success(array $data = [], int $status = 200, array $meta = []): void
    {
        self::send([
            'status' => 'success', // Backward compatibility
            'data' => $data,
            'error' => null,
            'meta' => array_merge([
                'timestamp' => date('c'),
            ], $meta),
        ], $status);
    }

    /**
     * Send error response
     */
    public static function error(
        string $message,
        int $status = 400,
        ?string $code = null,
        array $details = []
    ): void {
        self::send([
            'status' => 'error', // Backward compatibility
            'data' => null,
            'error' => [
                'code' => $code ?? self::getDefaultErrorCode($status),
                'message' => $message,
                'details' => $details,
            ],
            'meta' => [
                'timestamp' => date('c'),
            ],
        ], $status);
    }

    /**
     * Get default error code from HTTP status
     */
    private static function getDefaultErrorCode(int $status): string
    {
        return match ($status) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            409 => 'CONFLICT',
            422 => 'VALIDATION_ERROR',
            500 => 'INTERNAL_ERROR',
            default => 'ERROR',
        };
    }

    /**
     * Send JSON response
     */
    private static function send(array $payload, int $status): void
    {
        // Clear any output buffers to ensure clean JSON response
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        
        // Prevent any further output
        if (headers_sent()) {
            // If headers already sent, try to output JSON anyway
            echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

