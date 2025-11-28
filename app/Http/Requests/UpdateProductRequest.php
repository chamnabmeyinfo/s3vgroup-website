<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Request validation for updating a product
 */
final class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'categoryId' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'specs' => 'nullable|array',
            'heroImage' => 'nullable|string|url',
            'price' => 'nullable|numeric|min:0',
            'status' => 'in:DRAFT,PUBLISHED,ARCHIVED',
            'highlights' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'The product name cannot exceed 255 characters.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
            'status.in' => 'The status must be one of: DRAFT, PUBLISHED, ARCHIVED.',
        ];
    }
}

