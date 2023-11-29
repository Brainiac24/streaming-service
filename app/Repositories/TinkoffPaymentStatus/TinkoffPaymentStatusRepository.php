<?php

namespace App\Repositories\TinkoffPaymentStatus;

use App\Models\TinkoffPaymentStatus;
use App\Repositories\BaseRepository;

class TinkoffPaymentStatusRepository extends BaseRepository
{
    public function __construct(public TinkoffPaymentStatus $tinkoffPaymentStatus)
    {
        parent::__construct($tinkoffPaymentStatus);
    }
}
