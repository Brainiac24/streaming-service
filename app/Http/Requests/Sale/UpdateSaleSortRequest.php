<?php

namespace App\Http\Requests\Sale;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleSortRequest extends FormRequest
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
            'sales' => 'required|array',
            'sales.*.id' => 'required|integer|min:0',
            'sales.*.sort' => 'required|integer|min:0',
        ];
    }
}
