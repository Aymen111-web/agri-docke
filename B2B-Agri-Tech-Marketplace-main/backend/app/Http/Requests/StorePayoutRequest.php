<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayoutRequest extends FormRequest
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
            'farmer_id'            => ['required', 'integer', 'exists:users,id'],
            'order_fulfillment_id' => ['required', 'integer', 'exists:order_fulfillments,id'],
            'amount'               => ['required', 'numeric', 'min:0.01'],
            'reference'            => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'farmer_id.exists'            => 'The specified farmer does not exist.',
            'order_fulfillment_id.exists'  => 'The specified order fulfillment does not exist.',
            'amount.min'                  => 'Payout amount must be at least 0.01.',
        ];
    }
}
