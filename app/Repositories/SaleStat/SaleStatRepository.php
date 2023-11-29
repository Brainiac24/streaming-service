<?php

namespace App\Repositories\SaleStat;

use App\Models\SaleStat;
use App\Repositories\BaseRepository;

class SaleStatRepository extends BaseRepository
{
    public function __construct(public SaleStat $saleStat)
    {
        parent::__construct($saleStat);
    }
}
