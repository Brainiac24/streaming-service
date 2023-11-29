<?php

namespace App\Http\Resources\EventSession;

use App\Http\Resources\BaseJsonResource;

class ChildrenEventSessionListResource extends BaseJsonResource
{
    public function __construct($eventSessions)
    {
        foreach ($eventSessions as $eventSession) {
            $this->data[] = [
                "id" => $eventSession->id,
                "name" => $eventSession->name,
                "event_session_status_id" => $eventSession->event_session_status_id,
                "stream_id" => $eventSession->stream_id,
                "start_at" => $eventSession->start_at,
                "parent_id" => $eventSession->event_session_id,
                "fare_id" => $eventSession->fare_id,
                "created_at" => $eventSession->created_at,
                "updated_at" => $eventSession->updated_at,
            ];
        }
    }
}
