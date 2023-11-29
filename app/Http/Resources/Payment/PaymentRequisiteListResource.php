<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\BaseJsonResource;

class PaymentRequisiteListResource extends BaseJsonResource
{
    public function __construct($paymentRequisites)
    {
        parent::__construct(data: $paymentRequisites);
        $this->data = [];

        foreach ($paymentRequisites as $requisite) {
            $this->data[] = $requisite;
        }
    }
}
