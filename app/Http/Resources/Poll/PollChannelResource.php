<?php

namespace App\Http\Resources\Poll;

use App\Constants\Roles;
use App\Http\Resources\BaseJsonResource;
use Auth;

class PollChannelResource extends BaseJsonResource
{
    public function __construct($data, $channelType)
    {
        $this->data = [
            'id' => $data['id'],
            'event_session_id' => $data['event_session_id'],
            'question' => $data['question'],
            'channel' => $data[$channelType],
            'is_multiselect' => $data['is_multiselect'],
            'is_public_results' => $data['is_public_results'],
            'poll_type_id' => $data['poll_type_id'],
            'poll_status_id' => $data['poll_status_id'],
            'start_at' => $data['start_at'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'options' => $data['options'],
        ];
    }
}
