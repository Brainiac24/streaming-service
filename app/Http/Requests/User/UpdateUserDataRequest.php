<?php

namespace App\Http\Requests\User;

use App\Constants\Languages;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'login' => 'nullable|string',
            'name' => 'required|string',
            'lastname' => 'required|string',
            'country_code' => 'nullable|string|max:8',
            'phone_code' => 'nullable|string|max:8',
            'phone' => 'nullable|string|min:6|max:12',
            'lang' => [
                'nullable',
                'string',
                'min:2',
                'max:2',
                Rule::in(Languages::AVAILABLE)
            ],
            'country' => 'nullable|string',
            'region' => 'nullable|string',
            'city' => 'nullable|string',
            'work_scope' => 'nullable|string',
            'work_company' => 'nullable|string',
            'work_division' => 'nullable|string',
            'work_position' => 'nullable|string',
            'education' => 'nullable|string',
        ];
    }
}
