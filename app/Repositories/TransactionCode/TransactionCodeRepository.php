<?php

namespace App\Repositories\TransactionCode;

use App\Models\TransactionCode;
use App\Repositories\BaseRepository;

class TransactionCodeRepository extends BaseRepository
{
    public function __construct(public TransactionCode $transactionCode)
    {
        parent::__construct($transactionCode);
    }
}
