<?php

namespace App\Http\Requests\EventAccess;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class DetachEventAccessRequest extends FormRequest
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
            'email' => 'required|string|email:rfc,dns',
            'role_id' => 'required|integer|min:0',
        ];
    }
}
