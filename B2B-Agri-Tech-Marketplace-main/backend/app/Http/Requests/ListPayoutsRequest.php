<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListPayoutsRequest extends FormRequest
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
            'status'     => ['sometimes', 'string', 'in:pending,processed,failed'],
            'farmer_id'  => ['sometimes', 'integer', 'exists:users,id'],
            'per_page'   => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in'         => 'Invalid payout status filter. Must be one of: pending, processed, failed.',
            'farmer_id.exists'  => 'The specified farmer does not exist.',
            'per_page.min'      => 'Per page must be at least 1.',
            'per_page.max'      => 'Per page must not exceed 100.',
        ];
    }
}
