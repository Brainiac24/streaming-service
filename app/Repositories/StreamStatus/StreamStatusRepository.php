<?php

namespace App\Repositories\StreamStatus;

use App\Models\StreamStatus;
use App\Repositories\BaseRepository;

class StreamStatusRepository extends BaseRepository
{
    public function __construct(public StreamStatus $streamStatus)
    {
        parent::__construct($streamStatus);
    }
}
