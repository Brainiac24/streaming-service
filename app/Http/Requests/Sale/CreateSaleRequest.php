<?php

namespace App\Http\Requests\Sale;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateSaleRequest extends FormRequest
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
            'title' => 'required|string|max:1024',
            'description' => 'nullable|string|max:2048',
            'button_text' => 'required|string|max:255',
            'url' => 'required|string|max:1024',
            'cover' => 'nullable|string',
            'is_publish' => 'nullable|boolean',
        ];
    }
}
