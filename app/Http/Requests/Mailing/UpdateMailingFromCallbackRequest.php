<?php

namespace App\Http\Requests\Mailing;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMailingFromCallbackRequest extends FormRequest
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
            'job_uuid' => 'nullable|string',
            'data_json' => 'nullable|array',
            'mailing_requisite_id' => 'nullable|integer',
            'message_template_id' => 'nullable|integer',
            'message_title' => 'nullable|string',
            'event_id' => 'nullable|integer',
            'event_session_id' => 'nullable|integer',
            'contact_group_id' => 'nullable|integer',
            'delay_count' => 'nullable|integer',
            'mailing_status_id' => 'nullable|integer',
        ];
    }
}
