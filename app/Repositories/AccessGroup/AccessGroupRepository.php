<?php

namespace App\Repositories\AccessGroup;

use App\Models\AccessGroup;
use App\Repositories\BaseRepository;

class AccessGroupRepository extends BaseRepository
{
    public function __construct(public AccessGroup $accessGroup)
    {
        parent::__construct($accessGroup);
    }
}
