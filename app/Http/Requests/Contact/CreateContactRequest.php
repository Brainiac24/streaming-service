<?php

namespace App\Http\Requests\Contact;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateContactRequest extends FormRequest
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
            'name' => 'nullable|string',
            'lastname' => 'nullable|string',
            'data_json' => 'nullable|array',
            'contact_group_id' => 'required|integer',
            'event_ticket_type_id'=> 'nullable|integer',
            'event_ticket_id'=>'nullable|integer'
        ];
    }
}
