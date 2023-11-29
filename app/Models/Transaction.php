<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Transaction extends BaseModel
{
    public $fillable = [
        'amount',
        'user_id',
        'transaction_code_id',
        'transaction_type_id',
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
        'amount' => 'double',
    ];

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function transaction_code()
    {
        return $this->belongsTo(TransactionCode::class);
    }

    public function scopeCurrentAuthedUser(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }
}
