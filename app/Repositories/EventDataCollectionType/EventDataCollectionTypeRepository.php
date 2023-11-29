<?php

namespace App\Repositories\EventDataCollectionType;

use App\Models\EventDataCollectionType;
use App\Repositories\BaseRepository;

class EventDataCollectionTypeRepository extends BaseRepository
{
    public function __construct(public EventDataCollectionType $eventDataCollectionType)
    {
        parent::__construct($eventDataCollectionType);
    }
}
