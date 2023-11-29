<?php

namespace App\Http\Resources\Poll;

use App\Constants\Roles;
use App\Http\Resources\BaseJsonResource;
use Auth;

class PollListResource extends BaseJsonResource
{
    public function __construct($data, $accessGroupId)
    {
        parent::__construct(data: $data);
        $this->data = [];

        foreach ($data as $item) {
            $this->data[] = [
                'id' => $item['id'],
                'event_session_id' => $item['event_session_id'],
                'question' => $item['question'],
                'channel' => Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]) ? $item['private_channel'] : $item['channel'],
                'is_multiselect' => $item['is_multiselect'],
                'is_public_results' => $item['is_public_results'],
                'poll_type_id' => $item['poll_type_id'],
                'poll_status_id' => $item['poll_status_id'],
                'start_at' => $item['start_at'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
                'options' => array_map(function ($option) {
                    if (isset($option['voted_users'][Auth::id()])) {
                        $option['is_current_user_voted'] = true;
                    } else {
                        $option['is_current_user_voted'] = false;
                    }
                    unset($option['voted_users']);
                    return $option;
                }, $item['options']),
            ];
        }
    }
}
