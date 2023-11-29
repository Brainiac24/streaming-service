<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseJsonResource;
use App\Services\Transaction\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TransactionController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'TransactionController:list',
        'findById' => 'TransactionController:findById',
        'create' => 'TransactionController:create',
        'update' => 'TransactionController:update',
        'delete' => 'TransactionController:delete'
    ];

    public function __construct(public TransactionService $transactionService)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->transactionService->list())
        );
    }


    public function findById($id)
    {
        //
    }


    public function create(Request $request)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function delete($id)
    {
        //
    }
}
