<?php

namespace App\Http\Requests\Admin\Event;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class AdminEventListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|integer',
            'date_to' => 'nullable|integer'
        ];
    }
}
