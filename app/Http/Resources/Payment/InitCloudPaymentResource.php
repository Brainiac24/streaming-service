<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\BaseJsonResource;

class InitCloudPaymentResource extends BaseJsonResource
{
    public function __construct(int $paymentId) {

        parent::__construct();
        $this->data = [
            'payment_id' => $paymentId
        ];
    }
}
