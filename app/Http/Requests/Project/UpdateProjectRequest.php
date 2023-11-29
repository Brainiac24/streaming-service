<?php

namespace App\Http\Requests\Project;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'name' => 'nullable|string|min:2',
            'link' => 'nullable|string|regex:/^[a-zA-Z0-9@_-]*$/|min:2|not_in:my,event,docs',
            'support_email' => 'nullable|string|email:rfc,dns',
            'support_name' => 'nullable|string|min:2',
            'support_phone' => 'nullable|string',
            'support_link' => 'nullable|string',
            'support_site' => 'nullable|string',
            'cover' => 'nullable|string',
            'project_status_id' => 'nullable|integer',
        ];
    }
}
