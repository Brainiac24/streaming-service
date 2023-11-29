<?php

namespace App\Models;

class Poll extends BaseModel
{
    public $fillable = [
        'event_session_id',
        'question',
        'channel',
        'private_channel',
        'is_multiselect',
        'is_public_results',
        'poll_type_id',
        'poll_status_id',
        'start_at',
    ];

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }
}
