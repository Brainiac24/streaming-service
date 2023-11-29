<?php

namespace App\Models;

class TinkoffPaymentNotification extends BaseModel
{

    public $fillable = [
        'request',
        'response_payment_id',
        'tinkoff_payment_id',
        'tinkoff_payment_status_id',
    ];

    public function tinkoffPayment()
    {
        return $this->belongsTo(TinkoffPayment::class);
    }
}
