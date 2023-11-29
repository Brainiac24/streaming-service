<?php

namespace App\Http\Requests\EventTicket;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventMultiTicketRequest extends FormRequest
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
            'ticket' => 'nullable|string|max:32',
            'event_ticket_status_id' => 'nullable|integer',
        ];
    }
}
