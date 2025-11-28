<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

use App\Domain\Exceptions\ValidationException;

/**
 * Simple validation system
 * 
 * Provides clean, Apple-like validation with friendly error messages
 */
final class Validator
{
    /**
     * Validate data against rules
     *
     * @param array<string, mixed> $data
     * @param array<string, string|array> $rules
     * @param array<string, string> $messages Custom error messages
     * @throws ValidationException
     */
    public static function validate(array $data, array $rules, array $messages = []): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleString) {
            $rulesArray = is_string($ruleString) ? explode('|', $ruleString) : $ruleString;
            $value = $data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $ruleParts = explode(':', $rule, 2);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;

                if (!self::passes($ruleName, $value, $ruleValue, $data)) {
                    $errorKey = "{$field}.{$ruleName}";
                    $errorMessage = $messages[$errorKey] 
                        ?? $messages[$field] 
                        ?? self::getDefaultMessage($field, $ruleName, $ruleValue);
                    
                    $errors[$field][] = $errorMessage;
                    break; // Stop at first failed rule for this field
                }
            }

            // If field passed validation, add to validated array
            if (!isset($errors[$field])) {
                $validated[$field] = self::normalizeValue($value, $rulesArray);
            }
        }

        if (!empty($errors)) {
            throw new ValidationException(
                'Validation failed. Please check your input.',
                $errors
            );
        }

        return $validated;
    }

    /**
     * Check if validation rule passes
     */
    private static function passes(string $rule, mixed $value, ?string $ruleValue, array $data): bool
    {
        return match ($rule) {
            'required' => self::validateRequired($value),
            'string' => is_string($value) || $value === null,
            'integer', 'int' => is_int($value) || (is_string($value) && ctype_digit($value)) || $value === null,
            'numeric' => is_numeric($value) || $value === null,
            'email' => $value === null || filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => $value === null || filter_var($value, FILTER_VALIDATE_URL) !== false,
            'min' => self::validateMin($value, $ruleValue),
            'max' => self::validateMax($value, $ruleValue),
            'in' => self::validateIn($value, $ruleValue),
            'array' => is_array($value) || $value === null,
            'boolean', 'bool' => is_bool($value) || in_array($value, ['0', '1', 'true', 'false'], true) || $value === null,
            default => true, // Unknown rule passes (for extensibility)
        };
    }

    /**
     * Validate required field
     */
    private static function validateRequired(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return !empty($value);
        }

        return true;
    }

    /**
     * Validate minimum value/length
     */
    private static function validateMin(mixed $value, ?string $min): bool
    {
        if ($value === null || $min === null) {
            return true;
        }

        $minValue = is_numeric($min) ? (float) $min : (int) $min;

        if (is_numeric($value)) {
            return (float) $value >= $minValue;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $minValue;
        }

        if (is_array($value)) {
            return count($value) >= $minValue;
        }

        return true;
    }

    /**
     * Validate maximum value/length
     */
    private static function validateMax(mixed $value, ?string $max): bool
    {
        if ($value === null || $max === null) {
            return true;
        }

        $maxValue = is_numeric($max) ? (float) $max : (int) $max;

        if (is_numeric($value)) {
            return (float) $value <= $maxValue;
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $maxValue;
        }

        if (is_array($value)) {
            return count($value) <= $maxValue;
        }

        return true;
    }

    /**
     * Validate value is in allowed list
     */
    private static function validateIn(mixed $value, ?string $allowed): bool
    {
        if ($value === null || $allowed === null) {
            return true;
        }

        $allowedValues = array_map('trim', explode(',', $allowed));
        return in_array((string) $value, $allowedValues, true);
    }

    /**
     * Normalize value based on rules
     */
    private static function normalizeValue(mixed $value, array $rules): mixed
    {
        // Convert to integer if integer rule
        if (in_array('integer', $rules, true) || in_array('int', $rules, true)) {
            return $value !== null ? (int) $value : null;
        }

        // Convert to float if numeric rule
        if (in_array('numeric', $rules, true) && $value !== null) {
            return (float) $value;
        }

        // Convert to boolean if boolean rule
        if (in_array('boolean', $rules, true) || in_array('bool', $rules, true)) {
            if ($value === null) {
                return null;
            }
            return in_array($value, [true, 'true', '1', 1], true);
        }

        // Trim strings
        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    /**
     * Get default error message
     */
    private static function getDefaultMessage(string $field, string $rule, ?string $ruleValue): string
    {
        $fieldName = self::humanizeFieldName($field);

        return match ($rule) {
            'required' => "The {$fieldName} field is required.",
            'string' => "The {$fieldName} must be a string.",
            'integer', 'int' => "The {$fieldName} must be an integer.",
            'numeric' => "The {$fieldName} must be a number.",
            'email' => "The {$fieldName} must be a valid email address.",
            'url' => "The {$fieldName} must be a valid URL.",
            'min' => "The {$fieldName} must be at least {$ruleValue}.",
            'max' => "The {$fieldName} may not be greater than {$ruleValue}.",
            'in' => "The {$fieldName} must be one of: {$ruleValue}.",
            'array' => "The {$fieldName} must be an array.",
            'boolean', 'bool' => "The {$fieldName} must be true or false.",
            default => "The {$fieldName} field is invalid.",
        };
    }

    /**
     * Convert field name to human-readable format
     */
    private static function humanizeFieldName(string $field): string
    {
        // Convert camelCase to "camel case"
        $field = preg_replace('/([a-z])([A-Z])/', '$1 $2', $field);
        
        // Convert snake_case to "snake case"
        $field = str_replace('_', ' ', $field);
        
        // Capitalize first letter
        return ucfirst(strtolower($field));
    }
}

