<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Infrastructure\Validation\Validator;

final class CreateThemeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'slug' => 'string|min:1|max:255',
            'description' => 'string|max:1000',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'config' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Theme name is required.',
            'name.string' => 'Theme name must be a string.',
            'slug.string' => 'Theme slug must be a string.',
            'config.required' => 'Theme config is required.',
            'config.array' => 'Theme config must be an object.',
        ];
    }
}

