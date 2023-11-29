<?php

namespace App\Repositories\FareType;

use App\Models\FareType;
use App\Repositories\BaseRepository;

class FareTypeRepository extends BaseRepository
{
    public function __construct(public FareType $fareType)
    {
        parent::__construct($fareType);
    }
}
