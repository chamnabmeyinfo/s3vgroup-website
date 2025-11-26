<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse
{
    public static function success(array $data = [], int $status = 200): void
    {
        self::send([
            'status' => 'success',
            'data'   => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $context = []): void
    {
        self::send([
            'status'  => 'error',
            'message' => $message,
            'context' => $context,
        ], $status);
    }

    private static function send(array $payload, int $status): void
    {
        // Clear any output buffers to ensure clean JSON response
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

