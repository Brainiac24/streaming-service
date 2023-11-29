<?php

namespace App\Http\Requests\Mailing;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMailingRequest extends FormRequest
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
            'message_template_id' => 'nullable|integer',
            'message_title' => 'nullable|string',
            'event_id' => 'nullable|integer',
            'event_session_id' => 'nullable|integer',
            'contact_group_id' => 'nullable|integer',
            'mailing_status_id' => 'nullable|integer',
            'delay_count' => 'integer|required_if:send_at,null|nullable',
            'send_at' => 'integer|required_if:delay_count,null|nullable',
        ];
    }
}
