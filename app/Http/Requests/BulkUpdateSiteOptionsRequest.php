<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Request validation for bulk updating site options
 */
final class BulkUpdateSiteOptionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bulk' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'bulk.required' => 'The bulk options array is required.',
            'bulk.array' => 'The bulk field must be an array.',
        ];
    }

    /**
     * Get bulk options array
     *
     * @return array<string, mixed>
     */
    public function getBulkOptions(): array
    {
        $data = $this->getData();
        return $data['bulk'] ?? [];
    }
}

