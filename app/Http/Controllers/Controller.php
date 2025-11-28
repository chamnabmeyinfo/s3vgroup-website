<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ExceptionHandler;
use App\Http\Requests\FormRequest;

/**
 * Base controller class
 * 
 * Provides common functionality for all controllers
 */
abstract class Controller
{
    /**
     * Handle request with automatic exception handling
     */
    protected function handle(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            ExceptionHandler::handle($exception);
        }
    }

    /**
     * Validate request and return validated data
     *
     * @param class-string<FormRequest> $requestClass
     * @return array<string, mixed>
     */
    protected function validate(string $requestClass): array
    {
        $request = new $requestClass();
        return $request->validated();
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        Authenticate::requireAuth();
    }

    /**
     * Send success response
     */
    protected function success(array $data = [], int $status = 200, array $meta = []): void
    {
        JsonResponse::success($data, $status, $meta);
    }

    /**
     * Send error response
     */
    protected function error(string $message, int $status = 400, ?string $code = null, array $details = []): void
    {
        JsonResponse::error($message, $status, $code, $details);
    }
}

