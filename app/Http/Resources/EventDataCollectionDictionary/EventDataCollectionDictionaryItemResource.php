<?php

namespace App\Http\Resources\EventDataCollectionDictionary;

use App\Http\Resources\BaseJsonResource;

class EventDataCollectionDictionaryItemResource extends BaseJsonResource
{
    public function __construct($eventDataCollectionDictionary)
    {
        $this->data = [
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
