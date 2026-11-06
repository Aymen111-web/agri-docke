<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolvePaymentExceptionRequest extends FormRequest
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
            'action'           => ['nullable', 'string', 'in:refund_buyer,release_farmer'],
            'resolution_notes' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.in'                 => 'Action must be either refund_buyer or release_farmer.',
            'resolution_notes.required' => 'Resolution notes are required when resolving or rejecting an exception.',
            'resolution_notes.max'      => 'Resolution notes must not exceed 2000 characters.',
        ];
    }
}
