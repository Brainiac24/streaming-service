<?php

namespace App\Http\Requests\Ban;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBanRequest extends FormRequest
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
            'user_id' => 'required|integer',
            'event_id' => 'required|integer',
        ];
    }
}
