<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
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
            'category_id'            => ['nullable', 'integer'],
            'title'                  => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'image'                  => ['nullable'],
            'images'                 => ['nullable', 'array', 'max:5'],
            'images.*'               => ['nullable', 'max:10240'], // 10MB max per image
            'unit'                   => ['required', 'string', 'max:50'],
            'price_per_unit'         => ['required', 'numeric', 'min:0.01'],
            'quantity_available'     => ['required', 'numeric', 'min:0'],
            'batch_number'           => ['nullable', 'string', 'max:50'],
            'harvest_date'           => ['nullable', 'date'],
            'quality_grade'          => ['nullable', 'string', 'max:50'],
            'region'                 => ['nullable', 'string', 'max:100'],
            'zone'                   => ['nullable', 'string', 'max:100'],
            'process'                => ['nullable', 'string', 'max:100'],
            'minimum_order_quantity' => ['nullable', 'numeric', 'min:0.001'],
            'price_valid_from'       => ['nullable', 'date'],
            'price_valid_until'      => ['nullable', 'date', 'after_or_equal:price_valid_from'],
            'reference_market_price' => ['nullable', 'numeric', 'min:0'],
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
        ];
    }
}
