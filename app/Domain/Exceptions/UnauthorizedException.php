<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception for unauthorized access
 */
final class UnauthorizedException extends DomainException
{
    protected string $errorCode = 'UNAUTHORIZED';

    public function __construct(
        string $message = 'Authentication required.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

