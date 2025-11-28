<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class UpdateThemeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string|min:1|max:255',
            'slug' => 'string|min:1|max:255',
            'description' => 'string|max:1000',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Theme name must be a string.',
            'slug.string' => 'Theme slug must be a string.',
            'config.array' => 'Theme config must be an object.',
        ];
    }
}

