<?php

namespace App\Repositories\EventDataCollectionDictionary;

use App\Models\EventDataCollectionDictionary;
use App\Repositories\BaseRepository;

class EventDataCollectionDictionaryRepository extends BaseRepository
{
    public function __construct(public EventDataCollectionDictionary $eventDataCollectionDictionary)
    {
        parent::__construct($eventDataCollectionDictionary);
    }
}
