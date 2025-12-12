<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'     => 'required|email|max:'.config('constants.MAX_LENGTH.EMAIL'),
            'password'  => 'required|string|max:'.config('constants.MAX_LENGTH.PASSWORD'),
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
            'email.required' => __('messages.validation.email_required'),
            'email.invalid' => __('messages.validation.email_invalid'),
            'password.required' => __('messages.validation.password_required'),
        ];
    }
}
