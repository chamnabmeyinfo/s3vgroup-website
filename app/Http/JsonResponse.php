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
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

