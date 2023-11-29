<?php

namespace App\Http\Requests\Mailing;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateMailingRequest extends FormRequest
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
            'mailing_requisite_id' => 'nullable|integer',
            'message_template_id' => 'required|integer',
            'message_title' => 'required|string',
            'event_id' => 'required|integer',
            'event_session_id' => 'required|integer',
            'contact_group_id' => 'required|integer',
            'mailing_status_id' => 'nullable|integer',
            'delay_count' => 'nullable|integer|required_if:send_at,null',
            'send_at' => 'nullable|integer|required_if:delay_count,null',
        ];
    }
}
