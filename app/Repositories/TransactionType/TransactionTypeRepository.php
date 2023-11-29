<?php

namespace App\Repositories\TransactionType;

use App\Models\TransactionType;
use App\Repositories\BaseRepository;

class TransactionTypeRepository extends BaseRepository
{
    public function __construct(public TransactionType $transactionType)
    {
        parent::__construct($transactionType);
    }
}
