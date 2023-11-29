<?php

namespace App\Models;

class Permission extends BaseModel
{

    public $fillable = [
        'name',
        'display_name',
        'description',
        'is_active'
    ];

    protected $hidden = ['pivot'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function accessGroups()
    {
        return $this->belongsToMany(AccessGroup::class, 'permission_user', 'permission_id', 'access_group_id');
    }
}
