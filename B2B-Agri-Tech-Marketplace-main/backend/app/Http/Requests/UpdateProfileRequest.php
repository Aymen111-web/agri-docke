<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'first_name'       => ['sometimes', 'string', 'max:255'],
            'second_name'      => ['sometimes', 'string', 'max:255'],
            'phone'            => ['sometimes', 'string', 'unique:users,phone,' . $userId],
            'current_password' => ['nullable', 'string', 'required_with:new_password'],
            'new_password'     => ['nullable', 'string', 'min:6'],
            'bank_code'        => ['nullable', 'string', 'max:50'],
            'bank_name'        => ['nullable', 'string', 'max:100'],
            'account_number'   => ['nullable', 'string', 'max:50'],
            'account_name'     => ['nullable', 'string', 'max:255'],
            'profile_photo'    => ['nullable'], // 5MB max
            'businessName'     => ['nullable', 'string', 'max:255'],
            'tinNumber'        => ['nullable', 'string', 'max:50'],
            'region'           => ['nullable', 'string', 'max:255'],
            'deliveryHub'      => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.max'  => 'First name must not exceed 255 characters.',
            'second_name.max' => 'Second name must not exceed 255 characters.',
            'phone.unique'    => 'This phone number is already registered.',
        ];
    }
}
