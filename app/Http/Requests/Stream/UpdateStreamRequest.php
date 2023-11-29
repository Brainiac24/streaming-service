<?php

namespace App\Http\Requests\Stream;

use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStreamRequest extends FormRequest
{

    public $startAt = null;
    public $endAt = null;
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
        $this->startAt = now()->timestamp;
        $this->endAt = now()->add(1, 'year')->timestamp;
        $dateRuleInterval =  $this->startAt . ',' . $this->endAt;
        return [
            'title' => 'nullable|string',
            'start_at' => 'nullable|integer|between:' . $dateRuleInterval,
            'is_dvr_enabled' => 'nullable|boolean',
            'is_dvr_out_enabled' => 'nullable|boolean',
            'is_fullhd_enabled' => 'nullable|boolean',
            'is_substream_date_changes_confirmed' => 'nullable|boolean'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'start_at'            => 'дата начала',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'start_at.between.numeric' => 'Значение поля :attribute должно быть между ' . Carbon::parse($this->startAt)->format('d.m.Y H:i:s') . ' и ' . Carbon::parse($this->endAt)->format('d.m.Y H:i:s') . '. ',
        ];
    }
}
