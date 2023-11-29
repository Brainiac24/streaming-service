<?php

namespace App\Http\Requests\Event;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventCoverImgRequest extends FormRequest
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
            'cover' => 'required|image|mimes:jpg,jpeg,png,gif,svg,webp,bmp|max:10240',
        ];
    }
}
