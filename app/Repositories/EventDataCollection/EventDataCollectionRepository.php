<?php

namespace App\Repositories\EventDataCollection;

use App\Models\EventDataCollection;
use App\Repositories\BaseRepository;

class EventDataCollectionRepository extends BaseRepository
{
    public function __construct(public EventDataCollection $eventDataCollection)
    {
        parent::__construct($eventDataCollection);
    }

    public function getByEventIdAndEventDataCollectionTemplateId($eventId, $eventDataCollectionTemplateId)
    {
        return $this->eventDataCollection
            ->where('event_id', $eventId)
            ->where('event_data_collection_template_id', $eventDataCollectionTemplateId)
            ->first();
    }
}