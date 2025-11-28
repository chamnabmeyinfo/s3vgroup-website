<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use RuntimeException;

/**
 * Base exception for infrastructure-level errors
 */
abstract class InfrastructureException extends RuntimeException
{
    protected string $errorCode = 'INFRASTRUCTURE_ERROR';

    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}

