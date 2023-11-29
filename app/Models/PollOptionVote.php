<?php

namespace App\Models;

class PollOptionVote extends BaseModel
{
    public $fillable = [
        'poll_option_id',
        'user_id',
    ];
}
