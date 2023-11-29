<?php

namespace App\Repositories\EventAccess;

use App\Models\EventAccess;
use App\Repositories\BaseRepository;

class EventAccessRepository extends BaseRepository
{
    public function __construct(public EventAccess $eventAccess)
    {
        parent::__construct($eventAccess);
    }
}
