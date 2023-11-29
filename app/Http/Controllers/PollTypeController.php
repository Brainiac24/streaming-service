<?php

namespace App\Http\Controllers;

use App\Http\Requests\PollType\CreatePollTypeRequest;
use App\Http\Requests\PollType\UpdatePollTypeRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\PollType\PollTypeRepository;
use Response;

class PollTypeController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'PollTypeController:list',
        'findById' => 'PollTypeController:findById',
        'create' => 'PollTypeController:create',
        'update' => 'PollTypeController:update',
        'delete' => 'PollTypeController:delete'
    ];

    public function __construct(public PollTypeRepository $pollTypeRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollTypeRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollTypeRepository->findById($id))
        );
    }

    public function create(CreatePollTypeRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollTypeRepository->create($request->validated()))
        );
    }


    public function update(UpdatePollTypeRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->pollTypeRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->pollTypeRepository->delete($id);

        return Response::apiSuccess();
    }
}
