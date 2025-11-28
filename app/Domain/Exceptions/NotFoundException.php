<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception for resource not found errors
 */
final class NotFoundException extends DomainException
{
    protected string $errorCode = 'NOT_FOUND';

    public function __construct(
        string $message = 'Resource not found.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

