<?php

namespace App\Enums\Payment;

enum PaymentRequisitesStatusEnum: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;
}
