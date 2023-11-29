<?php

namespace App\Repositories\EventSessionStatus;

use App\Models\EventSessionStatus;
use App\Repositories\BaseRepository;

class EventSessionStatusRepository extends BaseRepository
{
    public function __construct(public EventSessionStatus $eventSessionStatus)
    {
        parent::__construct($eventSessionStatus);
    }
}
