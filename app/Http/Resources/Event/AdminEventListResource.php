<?php

namespace App\Http\Resources\Event;

use App\Constants\ImagePlaceholders;
use App\Constants\Roles;
use App\Http\Resources\BaseJsonResource;

class AdminEventListResource extends BaseJsonResource
{

    public function __construct($events)
    {
        parent::__construct(data: $events);
        $this->data = [];

        foreach ($events as $event) {
            $isCoverImgNotUploaded = empty($event->cover_img_path);

            $this->data[] = [
                "id" => $event->id,
                "name" => $event->name,
                "cover_img_path" => $isCoverImgNotUploaded ? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER : $event->cover_img_path,
                "is_cover_img_not_uploaded" => $isCoverImgNotUploaded,
                "link" => $event->link,
                "start_at" => $event->start_at,
                "event_status_id" => $event->event_status_id,
                "is_unique_ticket_enabled" => $event->is_unique_ticket_enabled,
                "is_multi_ticket_enabled" => $event->is_multi_ticket_enabled,
                "is_data_collection_enabled" => $event->is_data_collection_enabled,
                "user_id" => $event->user_id,
                "user_name" => $event->user_name,
                "user_lastname" => $event->user_lastname,
                "project_name" => $event->project_name,
                "project_link" => $event->project_link,
                "event_sessions_count" => $event->event_sessions_count,
                "created_at" => $event->created_at
            ];
        }
    }
}
