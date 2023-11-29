<?php

namespace App\Services\EventDataCollection;

use App\Repositories\EventDataCollection\EventDataCollectionRepository;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use Auth;

class EventDataCollectionService
{

    public function __construct(
        public EventDataCollectionRepository $eventDataCollectionRepository,
        public EventDataCollectionTemplateRepository $eventDataCollectionTemplateRepository
    ) {
    }

    public function create($data)
    {
        $data['user_id'] = Auth::id();
        return $this->eventDataCollectionRepository->create($data);
    }

    public function update($data, $id)
    {
        return $this->eventDataCollectionRepository->update($data, $id);
    }
}
