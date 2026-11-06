<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentExceptionRequest extends FormRequest
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
            'payment_id'  => ['nullable', 'integer', 'exists:payments,id'],
            'order_id'    => ['nullable', 'integer', 'exists:orders,id'],
            'type'        => ['required', 'string', 'in:dispute,mismatch,quality_mismatch,transport_delay,failed_payment_review,refund_request,other'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('payment_id') && !$this->filled('order_id')) {
                $validator->errors()->add('order_id', 'Either an order_id or payment_id must be provided.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_id.exists' => 'The specified payment does not exist.',
            'order_id.exists'   => 'The specified order does not exist.',
            'type.in'           => 'Exception type must be a valid dispute category.',
            'description.max'   => 'Description must not exceed 2000 characters.',
        ];
    }
}
