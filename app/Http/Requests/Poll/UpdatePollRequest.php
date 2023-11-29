<?php

namespace App\Http\Requests\Poll;

use App\Constants\PollOptionActions;
use Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePollRequest extends FormRequest
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
            'question' => 'nullable|string|max:255',
            'is_multiselect' => 'nullable|boolean',
            'is_public_results' => 'nullable|boolean',
            'is_publish' => 'nullable|boolean',
            'poll_type_id' => 'nullable|integer|min:0',
            'options' => 'nullable|array|min:2',
            'options.*.id' => 'nullable|integer|min:0',
            'options.*.name' => 'nullable|string',
            'options.*.action' => [
                'nullable',
                Rule::in(PollOptionActions::ACTIONS)
            ],
        ];
    }
}
