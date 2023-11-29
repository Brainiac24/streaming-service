<?php

namespace App\Http\Requests\EventSessionVisit;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UserStatsEventSessionVisitRequest extends FormRequest
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
            'eventSessionId' => 'required|integer'
        ];
    }
}