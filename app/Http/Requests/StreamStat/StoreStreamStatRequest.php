<?php

namespace App\Http\Requests\StreamStat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreStreamStatRequest extends FormRequest
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
            'stream_id' => 'required|integer',
            'is_playing' => 'required|boolean',
            'data' => 'required|array',
        ];
    }
}
