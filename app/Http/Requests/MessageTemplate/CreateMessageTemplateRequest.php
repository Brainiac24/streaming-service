<?php

namespace App\Http\Requests\MessageTemplate;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateMessageTemplateRequest extends FormRequest
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
            'html' => 'nullable|string',
            'text' => 'nullable|string',
            'blade_path' => 'nullable|string',
            'data_json' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ];
    }
}
