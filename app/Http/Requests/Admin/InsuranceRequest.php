<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InsuranceRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Make sure to allow the request
    }

    public function rules()
    {
        $insuranceId = $this->route('insurance');

        return [
            'abbreviation' => [
                'required',
                Rule::unique('insurances', 'abbreviation')
                    ->ignore($insuranceId)
                    ->where(function ($query) {
                        return $query->whereNull('clinic_id');
                    }),
                'max:'.config('constants.MAX_LENGTH.ABBREVIATION'),
            ],
            'common_name' => [
                'required',
                Rule::unique('insurances', 'common_name')
                    ->ignore($insuranceId)
                    ->where(function ($query) {
                        return $query->whereNull('clinic_id');
                    }),
                'max:'.config('constants.MAX_LENGTH.COMMON_NAME'),
            ],
            'admin_status' => 'required|in:approved,inactive',
        ];
    }
}
