<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\BaseJsonResource;

class InitTinkoffPaymentResource extends BaseJsonResource
{
    public function __construct($paymentUrl) {
        $this->data = [
            'payment_url' => $paymentUrl
        ];
    }
}
