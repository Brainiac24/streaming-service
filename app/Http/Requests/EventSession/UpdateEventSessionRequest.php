<?php

namespace App\Http\Requests\EventSession;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventSessionRequest extends FormRequest
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
            'code' => 'nullable|string|alpha_num',
            'sort' => 'nullable|numeric',
            'parent_id' => 'nullable|integer',
            'event_session_status_id' => 'nullable|integer',
            'config.is_messages_enabled' => 'nullable|boolean',
            'config.is_questions_enabled' => 'nullable|boolean',
            'config.is_question_messages_enabled' => 'nullable|boolean',
            'config.is_question_moderation_enabled' => 'nullable|boolean',
            'config.is_polls_enabled' => 'nullable|boolean',
            'config.is_sales_enabled' => 'nullable|boolean',
            'config.sales_title' => 'nullable|string',
        ];
    }
}
