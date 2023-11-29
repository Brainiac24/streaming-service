<?php

namespace App\Http\Requests\ChatMessage;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChatMessageRequest extends FormRequest
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
            'text' => 'required|string',
            'reply_to_chat_message_id' => 'nullable|integer',
            'chat_message_type_id' => 'nullable|integer'
        ];
    }
}
