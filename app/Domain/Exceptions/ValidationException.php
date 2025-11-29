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
     * @var array<string, string[]>
     */
    private $errors;

    /**
     * @param array<string, string[]> $errors Field errors
     */
    public function __construct(
        string $message = 'Validation failed.',
        array $errors = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->errors = $errors;
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

