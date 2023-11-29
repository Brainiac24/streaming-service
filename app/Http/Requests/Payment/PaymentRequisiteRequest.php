<?php

namespace App\Http\Requests\Payment;

use App\Enums\Payment\PaymentRequisitesServiceEnum;
use App\Rules\PaymentRequisiteAddRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class PaymentRequisiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service' => ['required', new Enum(PaymentRequisitesServiceEnum::class)],
            'project_id' => ['required', 'exists:projects,id'],
            'public_api_key' => ['required', 'string', new PaymentRequisiteAddRule($this->public_api_key, $this->private_api_key, $this->service)],
            'private_api_key' => ['required', 'string']
        ];
    }
}
