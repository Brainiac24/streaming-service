<?php

namespace App\Http\Requests\User;

use App\Constants\Languages;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuestDataRequest extends FormRequest
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
            'name' => 'required|string',
            'lastname' => 'nullable|string',
            'contact_email' => 'nullable|string|email:rfc,dns',
            'lang' => [
                'nullable',
                'string',
                'min:2',
                'max:2',
                Rule::in(Languages::AVAILABLE)
            ],
        ];
    }
}
