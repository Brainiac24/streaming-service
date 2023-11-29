<?php

namespace App\Models;

class EventAccess extends BaseModel
{

    public $fillable = [
        'event_id',
        'user_id',
        'role_id',
        'email',
    ];
}
