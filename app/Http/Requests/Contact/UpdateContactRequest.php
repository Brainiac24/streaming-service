<?php

namespace App\Http\Requests\Contact;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
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
            'name' => 'nullable|string',
            'lastname' => 'nullable|string',
            'data_json' => 'nullable|array',
            'user_id' => 'nullable|integer',
            'contact_group_id' => 'required|integer',
        ];
    }
}
