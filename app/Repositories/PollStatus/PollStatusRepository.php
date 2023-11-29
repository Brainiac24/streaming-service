<?php

namespace App\Repositories\PollStatus;

use App\Models\PollStatus;
use App\Repositories\BaseRepository;

class PollStatusRepository extends BaseRepository
{
    public function __construct(public PollStatus $pollStatus)
    {
        parent::__construct($pollStatus);
    }
}
