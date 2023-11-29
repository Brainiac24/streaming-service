<?php

namespace App\Models;

class MessageTemplate extends BaseModel
{
    public $fillable = [
        'name',
        'html',
        'text',
        'blade_path',
        'data_json',
        'is_active',
    ];

    protected $casts = [
        'data_json' => 'array'
    ];
}
