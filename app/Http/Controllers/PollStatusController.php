<?php

namespace App\Http\Controllers;

use App\Http\Requests\PollStatus\CreatePollStatusRequest;
use App\Http\Requests\PollStatus\UpdatePollStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\PollStatus\PollStatusRepository;
use Response;

class PollStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'PollStatusController:list',
        'findById' => 'PollStatusController:findById',
        'create' => 'PollStatusController:create',
        'update' => 'PollStatusController:update',
        'delete' => 'PollStatusController:delete'
    ];

    public function __construct(public PollStatusRepository $pollStatusRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollStatusRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollStatusRepository->findById($id))
        );
    }


    public function create(CreatePollStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollStatusRepository->create($request->validated()))
        );
    }


    public function update(UpdatePollStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollStatusRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->pollStatusRepository->delete($id);

        return Response::apiSuccess();
    }
}
