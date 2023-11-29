<?php

namespace App\Http\Requests\MessageBroker;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageTemplateRequest extends FormRequest
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
            'message' => 'required',
            'message.title' => 'required|string',
            'message.html_template' => 'required|string',
            'message.text_template' => 'nullable|string',
        ];
    }
}
