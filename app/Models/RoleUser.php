<?php

namespace App\Models;

class RoleUser extends BaseModel
{
    protected $table = 'role_user';
    public $fillable = [
        'role_id',
        'user_id',
        'access_group_id',
        'event_ticket_id',
        'is_active'
    ];
}
