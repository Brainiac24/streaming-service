<?php

namespace App\Repositories\TinkoffPayment;

use App\Models\TinkoffPayment;
use App\Repositories\BaseRepository;

class TinkoffPaymentRepository extends BaseRepository
{
    public function __construct(public TinkoffPayment $tinkoffPayment)
    {
        parent::__construct($tinkoffPayment);
    }
}
