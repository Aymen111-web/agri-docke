<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListUsersRequest extends FormRequest
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
            'account_status' => ['sometimes', 'string', 'in:active,suspended'],
            'capability'     => ['sometimes', 'string', 'in:farmer,buyer'],
            'search'         => ['sometimes', 'string', 'max:255'],
            'per_page'       => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_status.in' => 'Invalid account status filter. Must be one of: active, suspended.',
            'capability.in'     => 'Invalid capability filter. Must be one of: farmer, buyer.',
            'search.max'        => 'Search term must not exceed 255 characters.',
            'per_page.min'      => 'Per page must be at least 1.',
            'per_page.max'      => 'Per page must not exceed 100.',
        ];
    }
}
