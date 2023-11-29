<?php

namespace App\Http\Requests\Language;

use App\Constants\Languages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
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
            'lang' => [
                'nullable',
                'string',
                'min:2',
                'max:2',
                Rule::in(Languages::AVAILABLE)
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['lang' => ($this->route('lang') ?? Languages::RU)]);
    }
}
