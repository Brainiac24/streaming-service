<?php

namespace App\Http\Requests\Fare;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFareRequest extends FormRequest
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
            'name' =>'required|string|max:255',
            'description' =>'nullable|string',
            'price' =>'required|numeric|min:0',
            'old_price' =>'required|numeric|min:0',
            'fare_type_id' =>'required|integer|min:0',
            'config' =>'required|array',
            'config.viewers_count' =>'required|integer|min:0',
            'config.quality' =>'required|string',
            'config.storage_duration_amount' =>'required|numeric',
            'config.storage_duration_unit' =>'required|string',
            'config.moderators_count' =>'required|integer|min:0',
            'config.is_selected' =>'required|boolean',
            'config.is_unique_tickets_enabled' =>'required|boolean',
            'config.is_sell_buttons_enabled' =>'required|boolean',
            'config.is_fullhd_enabled' =>'required|boolean',
            'config.is_update_logo_enabled' =>'required|boolean',
            'config.is_unique_url_enabled' =>'required|boolean',
            'is_active' =>'nullable|boolean',
        ];
    }
}
