<?php

namespace App\Repositories\EventStatus;

use App\Models\EventStatus;
use App\Repositories\BaseRepository;

class EventStatusRepository extends BaseRepository
{
    public function __construct(public EventStatus $eventStatus)
    {
        parent::__construct($eventStatus);
    }
}
