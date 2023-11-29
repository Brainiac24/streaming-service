<?php

namespace App\Http\Requests\Sale;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
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
            'title' => 'nullable|string|max:1024',
            'description' => 'nullable|string|max:2048',
            'button_text' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:1024',
            'cover' => 'nullable|string',
            'is_delete_cover' => 'nullable|boolean',
        ];
    }
}
