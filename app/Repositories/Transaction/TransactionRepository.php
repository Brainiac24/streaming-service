<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Request;

class TransactionRepository extends BaseRepository
{
    public function __construct(public Transaction $transaction)
    {
        parent::__construct($transaction);
    }

    public function allWithPaginationForCurrentAuthedUser()
    {
        $perPage = (int)Request::get('perPage', 10);
        $page = (int)Request::get('page', 1);

        return $this->transaction
            ->with('transaction_type')
            ->with('transaction_code')
            ->currentAuthedUser()
            ->withQueryFilters('desc', 'id')
            ->paginate(perPage: $perPage, columns: ['transactions.*'], page: $page);
    }

    public function allWithPaginationByUserId($user_id)
    {
            $perPage = (int)Request::get('perPage', 10);
            $page = (int)Request::get('page', 1);

            return $this->transaction
                ->with('transaction_type')
                ->with('transaction_code')
                ->whereUserId($user_id)
                ->withQueryFilters('desc', 'id')
                ->paginate(perPage: $perPage, columns: ['transactions.*'], page: $page);
    }
}
