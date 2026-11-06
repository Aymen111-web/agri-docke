<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogForResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type'     => ['required', 'string', 'max:255'],
            'id'       => ['required', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required'  => 'The auditable model type is required (e.g. App\\Models\\Order).',
            'type.max'       => 'Auditable type must not exceed 255 characters.',
            'id.required'    => 'The auditable resource ID is required.',
            'id.min'         => 'Auditable ID must be a positive integer.',
            'per_page.min'   => 'Per page must be at least 1.',
            'per_page.max'   => 'Per page must not exceed 100.',
        ];
    }
}
