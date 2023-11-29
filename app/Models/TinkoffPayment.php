<?php

namespace App\Models;

class TinkoffPayment extends BaseModel
{
    public $fillable = [
        'user_id',
        'amount',
        'config_json',
        'response_payment_id',
        'payment_url',
        'tinkoff_payment_status_id',
        'request',
        'response',
    ];
    protected $casts = [
        'request' => 'array',
        'config_json' => 'array',
        'amount' => 'double',
    ];
}
