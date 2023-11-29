<?php

namespace App\Http\Requests\MailingRequisite;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateMailingRequisiteRequest extends FormRequest
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
            'project_id' => 'required|integer',
            'mailer' => 'nullable|string',
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|string',
            'from_name' => 'nullable|string',
            'token' => 'nullable|string',
            'data_json' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
