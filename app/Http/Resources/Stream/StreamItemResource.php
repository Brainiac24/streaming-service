<?php

namespace App\Http\Resources\Stream;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;

class StreamItemResource extends BaseJsonResource
{
    public function __construct($stream)
    {

        $isCoverImgNotUploaded = empty($stream->cover_img_path);

        $input = $stream->input;

        $this->data = [
            "id" => $stream->id,
            "title" => $stream->title,
            "cover_img_path" => $isCoverImgNotUploaded ? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER : $stream->cover_img_path,
            "is_cover_img_not_uploaded" => $isCoverImgNotUploaded,
            "user_id" => $stream->user_id,
            "start_at" => $stream->start_at,
            "last_auth_at" => $stream->last_auth_at,
            "user_connected_count" => $stream->user_connected_count,
            "rtmp_url" => $input['rtmp_url'],
            "rtmp_key" => $input['rtmp_key'] . '?sharedkey=' . $input['rtmp_sharedkey'],
            "stream_status_id" => $stream->stream_status_id,
            "is_onair" => $stream->is_onair,
            "onair_at" => $stream->onair_at,
            "is_dvr_enabled" => $stream->is_dvr_enabled,
            "is_dvr_out_enabled" => $stream->is_dvr_out_enabled,
            "is_fullhd_enabled" => $stream->is_fullhd_enabled,
            "parent_event_session_id" => $stream->parent_event_session_id,
            "created_at" => $stream->created_at,
            "updated_at" => $stream->updated_at,
        ];
    }
}
