<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Request;
use App\Infrastructure\Validation\Validator;

/**
 * Base class for request validation
 * 
 * Extend this class to create validated request objects
 */
abstract class FormRequest
{
    protected array $data = [];

    /**
     * Get validation rules
     *
     * @return array<string, string|array>
     */
    abstract public function rules(): array;

    /**
     * Get custom error messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Validate and return validated data
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $data = $this->getData();
        $rules = $this->rules();
        $messages = $this->messages();

        return Validator::validate($data, $rules, $messages);
    }

    /**
     * Get request data
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        if (!empty($this->data)) {
            return $this->data;
        }

        // Try JSON first, then POST
        $json = Request::json();
        if (is_array($json)) {
            return $this->data = $json;
        }

        return $this->data = $_POST;
    }

    /**
     * Get a specific field value
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->getData();
        return $data[$key] ?? $default;
    }

    /**
     * Check if field exists
     */
    public function has(string $key): bool
    {
        $data = $this->getData();
        return isset($data[$key]);
    }
}

