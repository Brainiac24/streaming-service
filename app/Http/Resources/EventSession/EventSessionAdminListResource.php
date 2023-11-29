<?php

namespace App\Http\Resources\EventSession;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;

class EventSessionAdminListResource extends BaseJsonResource
{
    public function __construct($eventSessions)
    {
        parent::__construct(data: $eventSessions);
        $this->data = [];

        foreach ($eventSessions as $eventSession) {
            $cover_img_path = $eventSession->stream_cover_img_path ?? $eventSession->event_cover_img_path;

            $this->data[] = [
                "session_id" => $eventSession->id,
                "start_at" => $eventSession->start_at,
                "user_id" => $eventSession->user_id,
                "user_fullname" => $eventSession->user_lastname . ' ' . $eventSession->user_name ,
                "cover_img_path" => $cover_img_path ?? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER,
                "logo_img_path" => $eventSession->logo_img_path ?? ImagePlaceholders::LOGO_PLACEHOLDER,
                "project_id" => $eventSession->project_id,
                "project_name" => $eventSession->project_name,
                "event_id" => $eventSession->event_id,
                "event_name" => $eventSession->event_name,
                "session_name" => $eventSession->name,
                "event_session_status_id" => $eventSession->event_session_status_id,
                "stream_id" => $eventSession->stream_id,
                "stream_key" => $eventSession->key,
                "onair_at" => $eventSession->onair_at,
                "is_onair" => $eventSession->is_onair,
                "user_connected_count" => $eventSession->user_connected_count,
                "parent_id" => $eventSession->event_session_id,
                "fare_name" => $eventSession->fare_name,
                "created_at" => $eventSession->created_at,
                "updated_at" => $eventSession->updated_at,
                "config_json" => $eventSession->config_json,
            ];
        }
    }
}
