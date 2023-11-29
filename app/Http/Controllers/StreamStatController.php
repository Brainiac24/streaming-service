<?php

namespace App\Http\Controllers;

use App\Http\Requests\StreamStat\StoreStreamStatRequest;
use App\Services\StreamStat\StreamStatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class StreamStatController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'StreamStatController:list',
        'findById' => 'StreamStatController:findById',
        'create' => 'StreamStatController:create',
        'update' => 'StreamStatController:update',
        'delete' => 'StreamStatController:delete'
    ];

    public function __construct(public StreamStatService $streamStatService)
    {
        //
    }

    public function list()
    {
        //
    }


    public function findById($id)
    {
        //
    }


    public function create(StoreStreamStatRequest $request)
    {
        $this->streamStatService->createWithStreamId($request->validated());

        return Response::apiSuccess();
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