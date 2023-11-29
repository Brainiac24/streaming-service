<?php

namespace App\Repositories\SaleStatus;

use App\Models\SaleStatus;
use App\Repositories\BaseRepository;

class SaleStatusRepository extends BaseRepository
{
    public function __construct(public SaleStatus $saleStatus)
    {
        parent::__construct($saleStatus);
    }
}
