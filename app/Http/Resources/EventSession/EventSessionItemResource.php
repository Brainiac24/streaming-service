<?php

namespace App\Http\Resources\EventSession;

use App\Constants\ImagePlaceholders;
use App\Constants\Roles;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Fare\FareItemResource;
use Auth;

class EventSessionItemResource extends BaseJsonResource
{
    public function __construct($eventSession, $accessGroupId, $extraFare = null)
    {
        $isLogoImgNotUploaded = empty($eventSession->logo_img_path);

        $this->data = [
            "id" => $eventSession->id,
            "event_id" => $eventSession->event_id,
            "name" => $eventSession->name,
            "content" => $eventSession->content,
            "logo_img_path" => $isLogoImgNotUploaded ? ImagePlaceholders::LOGO_PLACEHOLDER : $eventSession->logo_img_path,
            "is_logo_img_not_uploaded" => $isLogoImgNotUploaded,
            "config_json" => $eventSession->config_json,
            "event_session_status_id" => $eventSession->event_session_status_id,
            "stream_id" => $eventSession->stream_id,
            "chat_id" => $eventSession->chat_id,
            "parent_id" => $eventSession->event_session_id,
            "start_at" => $eventSession->stream->start_at,
            "sort" => $eventSession->sort,
            "fare_id" => $eventSession->fare_id,
            "code" => $eventSession->code,
            "event_link" => $eventSession->event_link,
            "project_link" => $eventSession->project_link,
            "key" => $eventSession->key,
            "channel" => Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]) ? $eventSession->private_channel : $eventSession->channel,
            "children" => (new ChildrenEventSessionListResource($eventSession?->children))->data,
            "created_at" => $eventSession->created_at,
            "updated_at" => $eventSession->updated_at,
        ];

        $this->meta = [
            'embed' => [
                'chat' => view('/event_sessions/chat', ["key" => $eventSession->key])->render(),
                'player' => view('/event_sessions/player', ["key" => $eventSession->key])->render(),
                'stream' => view('/event_sessions/stream', ["key" => $eventSession->key])->render(),
            ]
        ];

        if ($extraFare) {
            $this->meta['extra_fare'] = (new FareItemResource($extraFare))?->toArray()['data'] ?? [];
        }
    }
}
