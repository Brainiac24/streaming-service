<?php

namespace App\Models;

class AccessGroup extends BaseModel
{

    public $fillable = [
        'name',
        'display_name',
        'description',
        'is_active'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
