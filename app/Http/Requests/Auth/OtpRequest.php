<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'otp_code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/'
            ],
            'remember' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'No account found with this email address.',
            'otp_code.required' => 'OTP code is required.',
            'otp_code.size' => 'OTP code must be exactly 6 digits.',
            'otp_code.regex' => 'OTP code must contain only numbers.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'otp_code' => 'OTP code',
        ];
    }
}
