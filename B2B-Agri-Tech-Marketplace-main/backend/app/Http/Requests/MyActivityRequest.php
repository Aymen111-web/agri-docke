<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MyActivityRequest extends FormRequest
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
            'action'   => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.max'     => 'Action filter must not exceed 255 characters.',
            'per_page.min'   => 'Per page must be at least 1.',
            'per_page.max'   => 'Per page must not exceed 50.',
        ];
    }
}
