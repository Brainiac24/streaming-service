<?php

namespace App\Http\Requests\Contact;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UploadContactsRequest extends FormRequest
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
            'file' => 'required|mimes:xlsx,xls,csv',
            'contact_group_id' => 'nullable|integer',
            'event_ticket_type_id' => 'nullable|integer',
            'event_ticket_id' => 'nullable|integer'
        ];
    }
}
