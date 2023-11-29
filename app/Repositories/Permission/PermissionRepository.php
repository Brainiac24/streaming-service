<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use App\Repositories\BaseRepository;

class PermissionRepository extends BaseRepository
{
    public function __construct(public Permission $permission)
    {
        parent::__construct($permission);
    }
}
