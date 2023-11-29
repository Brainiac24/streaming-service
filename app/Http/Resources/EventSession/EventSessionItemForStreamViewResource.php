<?php

namespace App\Http\Resources\EventSession;

use App\Constants\ImagePlaceholders;
use App\Constants\Roles;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Chat\ChatItemRoomResource;
use App\Http\Resources\Role\RoleListResource;
use App\Http\Resources\Stream\StreamItemRoomResource;
use Auth;

class EventSessionItemForStreamViewResource extends BaseJsonResource
{
    public function __construct($eventSession, $accessGroupId)
    {
        $this->data = [
            "id" => $eventSession['id'],
            "event_id" => $eventSession['event_id'],
            "project_link" => $eventSession['project_link'],
            "name" => $eventSession['name'],
            "content" => $eventSession['content'],
            "logo_img_path" => $eventSession['logo_img_path'] ?? ImagePlaceholders::LOGO_PLACEHOLDER,
            "config_json" => $eventSession['config_json'],
            "event_session_status_id" => $eventSession['event_session_status_id'],
            "stream_id" => $eventSession['stream_id'],
            "parent_id" => $eventSession['event_session_id'],
            "start_at" => $eventSession['stream_start_at'],
            "fare_id" => $eventSession['fare_id'],
            "code" => $eventSession['code'],
            "channel" => Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]) ? $eventSession['private_channel'] : $eventSession['channel'],
            "created_at" => $eventSession['created_at'],
            "updated_at" => $eventSession['updated_at'],
            "is_user_banned" => $eventSession['is_user_banned'],
            "is_only" => $eventSession['is_only'],
            "roles" => (new RoleListResource($eventSession['roles']))->toArray()['data'] ?? [],
            "stream" => (new StreamItemRoomResource($eventSession['stream']))->toArray()['data'],
            "chat" => (new ChatItemRoomResource($eventSession['chat']))->toArray()['data'] ?? [],
            "polls" => $eventSession['polls'],
            "sales" => $eventSession['sales'],
            "chat_message_types" => $eventSession['chat_message_types'],
            "poll_statuses" => $eventSession['poll_statuses'],
            "poll_types" => $eventSession['poll_types'],
            "sale_statuses" => $eventSession['sale_statuses'],
            "stream_statuses" => $eventSession['stream_statuses'],
        ];
    }
}
