<?php

namespace App\Http\Resources\Event;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;
use Carbon\Carbon;

class EventItemResource extends BaseJsonResource
{
    public function __construct($event)
    {

        $isCoverImgNotUploaded = empty($event->cover_img_path);
        $isLogoImgNotUploaded = empty($event->logo_img_path);

        $this->data = [
            "id" => $event->id,
            "name" => $event->name,
            "project_id" => $event->project_id,
            "access_group_id" => $event->access_group_id,
            "cover_img_path" => $isCoverImgNotUploaded ? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER : $event->cover_img_path,
            "is_cover_img_not_uploaded" => $isCoverImgNotUploaded,
            "logo_img_path" => $isLogoImgNotUploaded ? ImagePlaceholders::LOGO_PLACEHOLDER : $event->logo_img_path,
            "is_logo_img_not_uploaded" => $isLogoImgNotUploaded,
            "description" => $event->description,
            "link" => $event->link,
            "project_link" => $event->project_link,
            "start_at" => $event->start_at,
            "end_at" => $event->end_at,
            "config" => $event->config_json,
            "event_status_id" => $event->event_status_id,
            "is_unique_ticket_enabled" => $event->is_unique_ticket_enabled,
            "is_multi_ticket_enabled" => $event->is_multi_ticket_enabled,
            "is_data_collection_enabled" => $event->is_data_collection_enabled,
            "payment_available" => (bool)$event->payment_requisites_status,
            "created_at" => $event->created_at,
            "updated_at" => $event->updated_at,
        ];
    }
}
