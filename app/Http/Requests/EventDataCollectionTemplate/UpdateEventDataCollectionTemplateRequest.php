<?php

namespace App\Http\Requests\EventDataCollectionTemplate;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventDataCollectionTemplateRequest extends FormRequest
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
            'event_id' => 'required|integer',
            'name' => 'nullable|string',
            'label' => 'required|string',
            'is_required' => 'nullable|boolean',
        ];
    }
}
