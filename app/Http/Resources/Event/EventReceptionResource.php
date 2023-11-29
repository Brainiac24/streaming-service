<?php

namespace App\Http\Resources\Event;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;

class EventReceptionResource extends BaseJsonResource
{
    public function __construct($eventData)
    {
        $event = $eventData['event'];

        $this->data = [
            'id' => $event['id'],
            'name' => $event['name'],
            'project_id' => $event['project_id'],
            'project_link' => $event['project_link'],
            'access_group_id' => $event['access_group_id'],
            'cover_img_path' => $event['cover_img_path'] ?? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER,
            'logo_img_path' => $event['logo_img_path'] ?? ImagePlaceholders::LOGO_PLACEHOLDER,
            'description' => $event['description'],
            'link' => $event['link'],
            'start_at' => $event['start_at'],
            'end_at' => $event['end_at'],
            'event_status_id' => $event['event_status_id'],
            'is_unique_ticket_enabled' => $event['is_unique_ticket_enabled'],
            'is_multi_ticket_enabled' => $event['is_multi_ticket_enabled'],
            'is_data_collection_enabled' => $event['is_data_collection_enabled'],
            'created_at' => $event['created_at'],
            'updated_at' => $event['updated_at'],
            'support' => [
                'name' => $event['projects_support_name'],
                'link' => $event['projects_support_link'],
                'phone' => $event['projects_support_phone'],
                'email' => $event['projects_support_email'],
                'site' => $event['projects_support_site'],
            ],
            'event_sessions' => array_values($eventData['event_sessions']),

        ];
    }
}
