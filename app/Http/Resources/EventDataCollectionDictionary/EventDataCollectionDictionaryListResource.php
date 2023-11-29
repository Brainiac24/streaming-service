<?php

namespace App\Http\Resources\EventDataCollectionDictionary;

use App\Http\Resources\BaseJsonResource;

class EventDataCollectionDictionaryListResource extends BaseJsonResource
{

    public function __construct($eventDataCollectionDictionaries)
    {

        parent::__construct(data: $eventDataCollectionDictionaries);
        $this->data = [];

        foreach ($eventDataCollectionDictionaries as $eventDataCollectionDictionary) {

            $this->data[] = [
                "id" => $eventDataCollectionDictionary->id,
                "name" => $eventDataCollectionDictionary->name,
                "label" => __($eventDataCollectionDictionary->label),
                "is_required" => $eventDataCollectionDictionary->is_required,
                "is_editable" => $eventDataCollectionDictionary->is_editable,
                "is_active" => $eventDataCollectionDictionary->is_active,
                "created_at" => $eventDataCollectionDictionary->created_at,
                "updated_at" => $eventDataCollectionDictionary->updated_at,
            ];
        }
    }
}
