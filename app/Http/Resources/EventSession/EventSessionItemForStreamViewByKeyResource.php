<?php

namespace App\Http\Resources\EventSession;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;

class EventSessionItemForStreamViewByKeyResource extends BaseJsonResource
{
    public function __construct($data, $accessGroupId)
    {
        if ($data['is_allowed_enter']) {
            $data['data'] = (new EventSessionItemForStreamViewResource($data['data'], $accessGroupId))?->toArray()['data'] ?? [];
        } else {

            $cover = $data['data']['stream_cover_img_path'];

            if (empty($cover)) {
                $cover = $data['data']['event_cover_img_path'];
            }

            if (empty($cover)) {
                $cover = ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER;
            }

            $data['data'] = [
                'event_session_id' => $data['data']['id'],
                'cover_img_path' => $cover,
                'event_id' => $data['data']['event_id'],
                'is_unique_ticket_enabled' => $data['data']['is_unique_ticket_enabled'],
                'is_multi_ticket_enabled' => $data['data']['is_multi_ticket_enabled'],
                'is_data_collection_enabled' => $data['data']['is_data_collection_enabled'],
                'data_collection' => $data['data']['data_collection'],
            ];
        }

        $this->data = $data;
    }
}
