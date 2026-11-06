<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayoutStatusRequest extends FormRequest
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
            'status'    => ['required', 'string', 'in:pending,processed,failed'],
            'reference' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'A payout status is required.',
            'status.in'       => 'Status must be one of: pending, processed, failed.',
            'reference.max'   => 'Reference must not exceed 255 characters.',
        ];
    }
}
