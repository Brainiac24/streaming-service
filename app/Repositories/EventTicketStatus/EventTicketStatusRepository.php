<?php

namespace App\Repositories\EventTicketStatus;

use App\Models\EventTicketStatus;
use App\Repositories\BaseRepository;

class EventTicketStatusRepository extends BaseRepository
{
    public function __construct(public EventTicketStatus $eventTicketStatus)
    {
        parent::__construct($eventTicketStatus);
    }
}
