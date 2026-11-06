<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAuditLogsRequest extends FormRequest
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
            'action'         => ['sometimes', 'string', 'max:255'],
            'user_id'        => ['sometimes', 'integer', 'exists:users,id'],
            'auditable_type' => ['sometimes', 'string', 'max:255'],
            'auditable_id'   => ['sometimes', 'integer', 'min:1'],
            'per_page'       => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists'      => 'The specified user does not exist.',
            'auditable_id.min'    => 'Auditable ID must be a positive integer.',
            'per_page.min'        => 'Per page must be at least 1.',
            'per_page.max'        => 'Per page must not exceed 100.',
        ];
    }
}
