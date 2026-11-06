<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation (normalize Ethiopian phone numbers).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = trim($this->input('phone'));
            $digits = preg_replace('/[^\d]/', '', $phone);

            if (preg_match('/^0([79]\d{8})$/', $digits, $matches)) {
                $normalized = '+251' . $matches[1];
            } elseif (preg_match('/^251([79]\d{8})$/', $digits, $matches)) {
                $normalized = '+' . $digits;
            } elseif (preg_match('/^([79]\d{8})$/', $digits, $matches)) {
                $normalized = '+251' . $matches[1];
            } else {
                $normalized = $phone;
            }

            $this->merge(['phone' => $normalized]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+251[0-9]{9}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid Ethiopian mobile phone number (e.g. 0911234567 or +251911234567).',
        ];
    }
}
