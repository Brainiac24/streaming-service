<?php

namespace App\Http\Requests\Event;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
        //$dateRuleInterval = now()->timestamp . ',' . now()->add(1, 'year')->timestamp;
        return [
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'link' => 'nullable|string|regex:/^[a-zA-Z0-9@_-]*$/',
            'start_at' => 'nullable|integer',//|between:' . $dateRuleInterval,
            'end_at' => 'nullable|integer', //|between:' . $dateRuleInterval,
            'is_unique_ticket_enabled' => 'nullable|boolean',
            'is_multi_ticket_enabled' => 'nullable|boolean',
            'is_data_collection_enabled' => 'nullable|boolean',
            'ticket_price' => 'nullable|numeric',
            'is_ticket_sales_enabled' => 'nullable|boolean',
        ];
    }
}
