<?php

namespace App\Models;

class Fare extends BaseModel
{

    public $fillable = [
        'name',
        'description',
        'price',
        'old_price',
        'is_selected',
        'fare_type_id',
        'config_json',
        'is_active',
    ];

    protected $casts = [
        'config_json' => 'array',
        'price' => 'float',
        'old_price' => 'float',
    ];
    public function fareType()
    {
        return $this->belongsTo(FareType::class);
    }
}
