<?php

namespace App\Models;

class Role extends BaseModel
{

    public $fillable = [
        'name',
        'display_name',
        'description',
        'is_active'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }
}
