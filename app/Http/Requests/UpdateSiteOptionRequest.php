<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Request validation for updating a site option
 */
final class UpdateSiteOptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'key_name' => 'nullable|string|max:255',
            'value' => 'nullable',
            'type' => 'in:text,textarea,number,boolean,json,color,image,url',
            'group_name' => 'in:general,design,contact,social,homepage,footer,advanced',
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'The type must be one of: text, textarea, number, boolean, json, color, image, url.',
            'group_name.in' => 'The group must be one of: general, design, contact, social, homepage, footer, advanced.',
            'priority.integer' => 'The priority must be an integer.',
        ];
    }
}

