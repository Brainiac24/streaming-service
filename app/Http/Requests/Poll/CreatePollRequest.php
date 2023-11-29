<?php

namespace App\Http\Requests\Poll;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreatePollRequest extends FormRequest
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
            'event_session_id' => 'required|integer|min:0',
            'question' => 'required|string|max:255',
            'is_multiselect' => 'nullable|boolean',
            'is_public_results' => 'nullable|boolean',
            'is_publish' => 'nullable|boolean',
            'poll_type_id' => 'nullable|integer|min:0',
            'options' => 'required|array|min:2',
            'options.*.name' => 'required|string',
        ];
    }
}
