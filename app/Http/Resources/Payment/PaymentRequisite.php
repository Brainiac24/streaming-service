<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\BaseJsonResource;

class PaymentRequisite extends BaseJsonResource
{
    public function __construct($paymentRequisites)
    {
        parent::__construct(data: $paymentRequisites);
        $this->data = $paymentRequisites;
    }
}
