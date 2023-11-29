<?php

namespace App\Http\Resources\Stream;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;

class StreamListResource extends BaseJsonResource
{

    public function __construct($streams)
    {
        foreach ($streams as $stream) {

            $isCoverImgNotUploaded = empty($stream->cover_img_path);

            $this->data[] = [
                "id" => $stream->id,
                "title" => $stream->title,
                "cover_img_path" => $isCoverImgNotUploaded ? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER : $stream->cover_img_path,
                "is_cover_img_not_uploaded" => $isCoverImgNotUploaded,
                "user_id" => $stream->user_id,
                "start_at" => $stream->start_at,
                "last_auth_at" => $stream->last_auth_at,
                "user_connected_count" => $stream->user_connected_count,
                "rtmp_url" => $stream->input['rtmp_url'],
                "rtmp_key" => $stream->input['rtmp_key'] . '?sharedkey=' . $stream->input['rtmp_sharedkey'],
                "stream_status_id" => $stream->stream_status_id,
                "is_onair" => (bool) $stream->is_onair,
                "is_dvr_enabled" => (bool) $stream->is_dvr_enabled,
                "is_dvr_out_enabled" => (bool) $stream->is_dvr_out_enabled,
                "is_fullhd_enabled" => (bool) $stream->is_fullhd_enabled,
                "created_at" => $stream->created_at,
                "updated_at" => $stream->updated_at,
            ];
        }
    }
}
