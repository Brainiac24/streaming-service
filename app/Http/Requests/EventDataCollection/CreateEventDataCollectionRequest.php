<?php


namespace App\Http\Requests\EventDataCollection;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventDataCollectionRequest extends FormRequest
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
            'event_id' => 'required|integer',
            'value' => 'required|string',
            'event_data_collection_template_id' => 'required|integer',
        ];
    }
}