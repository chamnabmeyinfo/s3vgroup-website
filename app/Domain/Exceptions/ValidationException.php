<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception for validation errors
 */
final class ValidationException extends DomainException
{
    protected string $errorCode = 'VALIDATION_ERROR';

    /**
     * @param array<string, string[]> $errors Field errors
     */
    public function __construct(
        string $message = 'Validation failed.',
        private readonly array $errors = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get field-specific errors
     *
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there are any field errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}

