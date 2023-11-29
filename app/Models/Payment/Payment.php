<?php

namespace App\Models\Payment;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Payment extends BaseModel
{
    use HasFactory;

    public $fillable = [
        'user_id',
        'event_id',
        'payment_requisite_id',
        'amount',
        'status',
        'type',
        'data_json'
    ];

    protected $casts = [
        'data_json' => 'array',
    ];
}
