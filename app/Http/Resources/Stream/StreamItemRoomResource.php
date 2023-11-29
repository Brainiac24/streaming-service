<?php

namespace App\Http\Resources\Stream;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;

class StreamItemRoomResource extends BaseJsonResource
{
    public function __construct($stream)
    {
        $this->data = [
            "id" => $stream['id'],
            "title" => $stream['title'],
            "cover_img_path" => empty($stream['cover_img_path']) ? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER : $stream['cover_img_path'],
            "start_at" => $stream['start_at'],
            "onair_at" => $stream['onair_at'],
            "user_connected_count" => $stream['user_connected_count'],
            "is_onair" => $stream['is_onair'],
            "is_dvr_enabled" => $stream['is_dvr_enabled'],
            "is_dvr_out_enabled" => $stream['is_dvr_out_enabled'],
            "is_fullhd_enabled" => $stream['is_fullhd_enabled'],
            "url" => $stream['url']
        ];
    }
}
