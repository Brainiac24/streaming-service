<?php

namespace App\Http\Resources\EventSession;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;

class EventSessionListResource extends BaseJsonResource
{
    public function __construct($eventSessions)
    {
        foreach ($eventSessions as $eventSession) {

            $isLogoImgNotUploaded = empty($eventSession->logo_img_path);

            $this->data[] = [
                "id" => $eventSession->id,
                "name" => $eventSession->name,
                "logo_img_path" => $isLogoImgNotUploaded ? ImagePlaceholders::LOGO_PLACEHOLDER : $eventSession->logo_img_path,
                "is_logo_img_not_uploaded" => $isLogoImgNotUploaded,
                "event_session_status_id" => $eventSession->event_session_status_id,
                "config_json" => $eventSession->config_json,
                "stream_id" => $eventSession->stream_id,
                "start_at" => $eventSession->start_at,
                "parent_id" => $eventSession->event_session_id,
                "fare_id" => $eventSession->fare_id,
                "children" => (new ChildrenEventSessionListResource($eventSession?->children))->data,
                "created_at" => $eventSession->created_at,
                "updated_at" => $eventSession->updated_at,
            ];
        }
    }
}
