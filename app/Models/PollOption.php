<?php

namespace App\Models;

class PollOption extends BaseModel
{
    public $fillable = [
        'name',
        'votes_count',
        'poll_id',
    ];
}
