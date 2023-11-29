<?php

namespace App\Http\Controllers;

use App\Http\Requests\StreamStatus\CreateStreamStatusRequest;
use App\Http\Requests\StreamStatus\UpdateStreamStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\StreamStatus\StreamStatusRepository;
use Illuminate\Support\Facades\Response;

class StreamStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'StreamStatusController:list',
        'findById' => 'StreamStatusController:findById',
        'create' => 'StreamStatusController:create',
        'update' => 'StreamStatusController:update',
        'delete' => 'StreamStatusController:delete'
    ];

    public function __construct(private StreamStatusRepository $streamStatusRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->streamStatusRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->streamStatusRepository->findById($id))
        );
    }


    public function create(CreateStreamStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->streamStatusRepository->create($request->validated()))
        );
    }


    public function update(UpdateStreamStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->streamStatusRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->streamStatusRepository->delete($id);

        return Response::apiSuccess();
    }
}
