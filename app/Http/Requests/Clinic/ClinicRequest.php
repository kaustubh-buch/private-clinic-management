<?php

namespace App\Http\Requests\Clinic;

use App\Rules\VerifyOTP;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ClinicRequest extends FormRequest
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
            'name'         => ['required', 'max:'.config('constants.MAX_LENGTH.CLINIC_NAME')],
            'address'      => ['required', 'max:'.config('constants.MAX_LENGTH.ADDRESS')],
            'software_id'  => ['required_without:original_software_id'],
            'other_software' => ['required_if:original_software_id,other', 'nullable', 'string', 'max:'.config('constants.MAX_LENGTH.OTHER_SOFTWARE')],
            'contact_no'   => ['required_unless:original_software_id,other', 'nullable', 'regex:'.config('constants.GLOBAL.PATTERNS.CONTACT_NO')],
            'mobile_no'    => ['required_unless:original_software_id,other', 'nullable', 'regex:'.config('constants.GLOBAL.PATTERNS.MOBILE_NO')],
            'state_id'  => ['required_unless:original_software_id,other', 'nullable'],
            'otp' => ['bail', 'required_unless:original_software_id,other', 'nullable', 'digits:'.config('constants.MAX_LENGTH.OTP'), new VerifyOTP],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('messages.validation.clinic_name_required'),
            'name.max' =>  __('messages.validation.clinic_name_max', ['maxlength' => config('constants.MAX_LENGTH.CLINIC_NAME')]),
            'address.required' => __('messages.validation.clinic_address_required'),
            'address.max' =>  __('messages.validation.address_max', ['maxlength' => config('constants.MAX_LENGTH.ADDRESS')]),
            'software_id.required_without' => __('messages.validation.software_id_required'),
            'other_software.required_if' => __('messages.validation.other_software_required'),
            'contact_no.required_unless' => __('messages.validation.contact_no_required'),
            'contact_no.regex' => __('messages.validation.contact_no_invalid'),
            'mobile_no.required_unless' => __('messages.validation.mobile_no_required'),
            'mobile_no.regex' => __('messages.validation.mobile_no_invalid'),
            'state_id.required_unless' => __('messages.validation.state_required'),
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->input('software_id') === 'other') {
            $this->merge([
                'software_id' => null,
                'original_software_id' => 'other', // optional: for rule reference
            ]);
        }
    }

    /**
     * Modify the validated data after validation passes.
     *
     * @return void
     */
    protected function passedValidation()
    {
        $this->merge([
            'software_notify_requested' => $this->input('original_software_id') === 'other' ? true : false,
            'user_id' => Auth::id(),
        ]);
    }
}
