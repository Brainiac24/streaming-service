<?php

namespace App\Http\Requests\ContactGroup;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactGroupRequest extends FormRequest
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
            'event_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }
}
