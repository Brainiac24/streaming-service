<?php

namespace App\Rules;

use App\Enums\Payment\PaymentRequisitesServiceEnum;
use App\Services\Payment\CloudPaymentService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PaymentRequisiteAddRule implements ValidationRule
{
    /**
     * @param string $publicApiKey
     * @param string $privateApiKey
     * @param int $service
     */
    public function __construct(public string $publicApiKey, public string $privateApiKey, public int $service)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $success = false;
        if($this->service === PaymentRequisitesServiceEnum::CLOUD_PAYMENTS->value) {
            /** @var  CloudPaymentService $paymentRequisiteService */
            $cloudPaymentService = app(CloudPaymentService::class);
            $success = $cloudPaymentService->checkApiKey($this->publicApiKey, $this->privateApiKey);
        }

        if(!$success) {
            $fail(trans('payment_requisite.public_private_key_not_valid'));
        }
    }
}
