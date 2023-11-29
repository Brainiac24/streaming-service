<?php

namespace App\Http\Requests\Nimble;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NimbleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'call' =>  Rule::in(['publish', 'update_publish']),
            'app' =>  Rule::in(['publisher']),
            'name' =>  'required|min:3',
            'sharedkey' => 'required|min:3'
        ];
    }
}
