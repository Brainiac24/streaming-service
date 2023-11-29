<?php

namespace App\Repositories\TinkoffPaymentNotification;

use App\Models\TinkoffPaymentNotification;
use App\Repositories\BaseRepository;

class TinkoffPaymentNotificationRepository extends BaseRepository
{
    public function __construct(public TinkoffPaymentNotification $tinkoffPaymentNotification)
    {
        parent::__construct($tinkoffPaymentNotification);
    }

    public function findByTinkoffPaymentStatusId($responsePaymentId, $tinkoffPaymentStatusId)
    {
        return $this->tinkoffPaymentNotification
            ->where('response_payment_id', '=', $responsePaymentId)
            ->where('tinkoff_payment_status_id', '=', $tinkoffPaymentStatusId)
            ->first();
    }
}
