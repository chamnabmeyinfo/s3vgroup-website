<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception for resource conflicts (e.g., duplicate entries)
 */
final class ConflictException extends DomainException
{
    protected string $errorCode = 'CONFLICT';

    public function __construct(
        string $message = 'Resource conflict.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

