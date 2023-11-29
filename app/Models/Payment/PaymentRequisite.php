<?php

namespace App\Models\Payment;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentRequisite extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'id',
        'project_id',
        'status',
        'service',
        'public_api_key',
        'private_api_key',
        'data_json'
    ];

    protected $casts = [
        'data_json' => 'array'
    ];
}
