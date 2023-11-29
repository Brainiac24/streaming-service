<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\UpdateEventCoverImgRequest;
use App\Http\Requests\Stream\UpdateStreamRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Stream\StreamItemResource;
use App\Http\Resources\Stream\StreamUpdateItemResource;
use App\Services\Stream\StreamService;
use DB;
use Illuminate\Http\Request;
use Response;

class StreamController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'StreamController:list',
        'findById' => 'StreamController:findById',
        'create' => 'StreamController:create',
        'update' => 'StreamController:update',
        'delete' => 'StreamController:delete'
    ];

    public function __construct(public StreamService $streamService)
    {
        //
    }

    public function list()
    {
        //
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new StreamItemResource($this->streamService->findById($id))
        );
    } 


    public function create(Request $request)
    {
        //
    }


    public function update(UpdateStreamRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $result = Response::apiSuccess(
                new StreamUpdateItemResource($this->streamService->update($request->validated(), $id))
            );
            if (DB::getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (DB::getPdo()->inTransaction()) {
                DB::rollBack();
            }

            throw $th;
        }
        return $result;


    }


    public function delete($id)
    {
        //
    }

    public function updateCoverImg(UpdateEventCoverImgRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->streamService->updateCoverImg($request->validated(), $id))
        );
    }

    public function deleteCoverImg($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->streamService->deleteCoverImg($id))
        );
    }
}
