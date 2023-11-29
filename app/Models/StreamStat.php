<?php

namespace App\Models;

class StreamStat extends BaseModel
{

    public $fillable = [
        'stream_id',
        'user_id',
        'is_playing',
        'data_json',
        'ip',
        'useragent',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
