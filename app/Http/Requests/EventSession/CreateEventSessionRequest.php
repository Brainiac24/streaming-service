<?php

namespace App\Http\Requests\EventSession;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventSessionRequest extends FormRequest
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
        $dateRuleInterval = now()->timestamp . ',' . now()->add(1, 'year')->timestamp;
        return [
            'name' => 'required|string',
            'start_at' => 'required|integer|between:' . $dateRuleInterval,
            //'end_at' => 'required|integer|between:' . $dateRuleInterval,
            'fare_id' => 'required|integer',
            'parent_id' => 'nullable|integer',
        ];
    }
}
