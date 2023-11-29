<?php

namespace App\Repositories\EventDataCollectionTemplate;

use App\Constants\CacheKeys;
use App\Models\EventDataCollectionTemplate;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class EventDataCollectionTemplateRepository extends BaseRepository
{
    public function __construct(public EventDataCollectionTemplate $eventDataCollectionTemplate)
    {
        parent::__construct($eventDataCollectionTemplate);
    }

    public function create(array $data)
    {
        $eventDataCollectionTemplate = parent::create($data);

        CacheServiceFacade::forget(CacheKeys::templateByEventIdKey($data['event_id']));

        return $eventDataCollectionTemplate;
    }

    public function update(array $data, $id)
    {
        $eventDataCollectionTemplate = parent::update($data, $id);

        CacheServiceFacade::forget(CacheKeys::templateByEventIdKey($data['event_id']));

        return $eventDataCollectionTemplate;
    }

    public function delete($id)
    {
        $eventDataCollectionTemplate = $this->eventDataCollectionTemplate->find($id);

        if (!$eventDataCollectionTemplate) {
            return true;
        }

        $result = parent::deleteByModel($eventDataCollectionTemplate);

        CacheServiceFacade::forget(CacheKeys::templateByEventIdKey($eventDataCollectionTemplate['event_id']));

        return $result;
    }

    public function getTemplatesByEventSessionId($eventSessionId)
    {
        return $this->eventDataCollectionTemplate
            ->currentAuthedUserByAuthedId()
            ->join('event_sessions',  function ($join) use ($eventSessionId) {
                $join
                    ->on('event_sessions.event_id', '=', 'events.id')
                    ->where('event_sessions.id', $eventSessionId);
            })
            ->orderBy('event_data_collection_templates.id', 'asc')
            ->get([
                'event_data_collection_templates.*',
                'events.is_data_collection_enabled as event_is_data_collection_enabled'
            ]);
    }

    public function getTemplatesByEventId($eventId)
    {
        $cacheKey = CacheKeys::templateByEventIdKey($eventId);

        $eventDataCollectionTemplates = CacheServiceFacade::get($cacheKey);

        if (empty($eventDataCollectionTemplates)) {
            $eventDataCollectionTemplates = $this->eventDataCollectionTemplate
                ->join('events',  function ($join) use ($eventId) {
                    $join
                        ->on('events.id', '=', 'event_data_collection_templates.event_id')
                        ->where('events.id', $eventId);
                })
                ->orderBy('event_data_collection_templates.id', 'asc')
                ->get([
                    'event_data_collection_templates.*'
                ]);

            $eventDataCollectionTemplateIds = [];
            foreach ($eventDataCollectionTemplates as $eventDataCollectionTemplate) {
                $eventDataCollectionTemplateIds[] = $eventDataCollectionTemplate['id'];
            }

            CacheServiceFacade::tags([
                CacheKeys::eventIdTag($eventId),
                ...CacheKeys::setEventDataCollectionTemplateIdTags($eventDataCollectionTemplateIds)
            ])
                ->set($cacheKey, $eventDataCollectionTemplates, config('cache.ttl'));
        }

        return $eventDataCollectionTemplates;
    }
}
