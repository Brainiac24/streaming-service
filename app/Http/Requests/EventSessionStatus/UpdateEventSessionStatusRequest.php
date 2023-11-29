<?php

namespace App\Http\Requests\EventSessionStatus;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventSessionStatusRequest extends FormRequest
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
            'name'=>'required|string',
            'is_active'=>'nullable|boolean'
        ];
    }
}
