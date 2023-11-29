<?php

namespace App\Enums\Payment;

enum PaymentStatusEnum: int
{
    case NEW = 0;
    case PAID = 1;
    case FAILED = 2;
    case PARTLY_PAID = 3;
}
