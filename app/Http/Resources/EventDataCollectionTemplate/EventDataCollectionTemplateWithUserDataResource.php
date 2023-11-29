<?php

namespace App\Http\Resources\EventDataCollectionTemplate;

use App\Http\Resources\BaseJsonResource;

class EventDataCollectionTemplateWithUserDataResource extends BaseJsonResource
{
    public function __construct($eventDataCollectionTemplates)
    {

        parent::__construct(data: $eventDataCollectionTemplates);
        $this->data = [];

        foreach ($eventDataCollectionTemplates as $eventDataCollectionTemplate) {

            $this->data[] = [
                "id" => $eventDataCollectionTemplate['id'],
                "name" => $eventDataCollectionTemplate['name'],
                "label" => __($eventDataCollectionTemplate['label']),
                "value" => $eventDataCollectionTemplate['value'] ?? '',
                "is_required" => $eventDataCollectionTemplate['is_required'],
                "is_editable" => $eventDataCollectionTemplate['is_editable']
            ];
        }
    }
}
