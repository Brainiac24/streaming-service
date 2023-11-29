<?php

namespace App\Repositories\NimbleStat;

use App\Models\NimbleStat;
use App\Repositories\BaseRepository;

class NimbleStatRepository extends BaseRepository
{
    public function __construct(public NimbleStat $nimbleStat)
    {
        parent::__construct($nimbleStat);
    }

    public function maxConnected($streamId)
    {
        return $this->nimbleStat->where("stream_id", $streamId)->max('connected_count');
    }

    public function listByStreamIdAndEventDateEndAt($streamId, $eventDateEndAt)
    {
        return $this->nimbleStat
            ->where("stream_id", $streamId)
            ->where('created_at', '<=', $eventDateEndAt)
            ->get();
    }
}