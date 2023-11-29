<?php

namespace App\Repositories\PollType;

use App\Models\PollType;
use App\Repositories\BaseRepository;

class PollTypeRepository extends BaseRepository
{
    public function __construct(public PollType $pollType)
    {
        parent::__construct($pollType);
    }
}
