<?php

namespace App\Http\Requests\MessageBroker;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSmtpCredentialsRequest extends FormRequest
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
            'smtp_credentials' => 'required',
            'smtp_credentials.transport' => 'required|string',
            'smtp_credentials.host' => 'required|string',
            'smtp_credentials.port' => 'required|numeric',
            'smtp_credentials.username' => 'required|string',
            'smtp_credentials.password' => 'required|string',
            'smtp_credentials.encryption' => 'nullable|string',
            'smtp_credentials.from_address' => 'required|string',
            'smtp_credentials.from_name' => 'required|string',
            'smtp_credentials.token' => 'nullable|string',
        ];
    }
}
