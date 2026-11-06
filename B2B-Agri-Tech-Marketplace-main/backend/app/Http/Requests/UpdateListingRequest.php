<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
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
            'category_id'            => ['sometimes', 'nullable', 'integer'],
            'title'                  => ['sometimes', 'string', 'max:255'],
            'description'            => ['sometimes', 'nullable', 'string', 'max:2000'],
            'image'                  => ['sometimes', 'nullable'],
            'images'                 => ['sometimes', 'nullable', 'array', 'max:5'],
            'images.*'               => ['nullable', 'max:10240'],
            'unit'                   => ['sometimes', 'string', 'max:50'],
            'price_per_unit'         => ['sometimes', 'numeric', 'min:0.01'],
            'quantity_available'     => ['sometimes', 'numeric', 'min:0'],
            'status'                 => ['sometimes', 'string', 'in:active,inactive'],
            'batch_number'           => ['sometimes', 'nullable', 'string', 'max:50'],
            'harvest_date'           => ['sometimes', 'nullable', 'date'],
            'quality_grade'          => ['sometimes', 'nullable', 'string', 'max:50'],
            'region'                 => ['sometimes', 'nullable', 'string', 'max:100'],
            'zone'                   => ['sometimes', 'nullable', 'string', 'max:100'],
            'process'                => ['sometimes', 'nullable', 'string', 'max:100'],
            'minimum_order_quantity' => ['sometimes', 'nullable', 'numeric', 'min:0.001'],
            'price_valid_from'       => ['sometimes', 'nullable', 'date'],
            'price_valid_until'      => ['sometimes', 'nullable', 'date', 'after_or_equal:price_valid_from'],
            'reference_market_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit.max'               => 'Unit must not exceed 50 characters.',
            'price_per_unit.min'    => 'Price per unit must be at least 0.01.',
            'quantity_available.min' => 'Quantity available cannot be negative.',
            'status.in'             => 'Status must be either active or inactive.',
        ];
    }
}
