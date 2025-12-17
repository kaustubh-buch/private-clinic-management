<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TemplateCreateRequest extends FormRequest
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
            'name' => 'required|max:'.config('constants.MAX_LENGTH.TEMPLATE_NAME'),
            'message' => 'required|max:'.config('constants.MAX_LENGTH.TEMPLATE_MESSAGE'),
        ];
    }

    public function messages()
    {
        return [
            'name.required' => trans('messages.validation.template_name_required'),
            'message.required' => trans('messages.validation.template_content_required'),
            'message.max' => trans('messages.validation.template_content_max_length'),
        ];
    }
}
