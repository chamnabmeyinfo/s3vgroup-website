<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception for forbidden access
 */
final class ForbiddenException extends DomainException
{
    protected string $errorCode = 'FORBIDDEN';

    public function __construct(
        string $message = 'Insufficient permissions.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

