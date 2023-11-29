<?php

namespace App\Http\Requests\ChatMessage;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateChatMessageRequest extends FormRequest
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
            'text' => 'required|string|max:2048',
            'reply_to_chat_message_id' => 'nullable|integer'
        ];
    }
}
