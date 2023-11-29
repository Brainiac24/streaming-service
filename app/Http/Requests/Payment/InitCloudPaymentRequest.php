<?php

namespace App\Http\Requests\Payment;

use App\Enums\Payment\PaymentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class InitCloudPaymentRequest extends FormRequest
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
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'payment_type' => ['required', new Enum(PaymentTypeEnum::class)],
            'event_id' => ['required', 'exists:events,id']
        ];
    }
}
