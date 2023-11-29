<?php

namespace App\Http\Requests\Event;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class ProjectLinkEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'project_link' => 'nullable|string',
        ];
    }
}
