<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventSessionStatus\CreateEventSessionStatusRequest;
use App\Http\Requests\EventSessionStatus\UpdateEventSessionStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\EventSessionStatus\EventSessionStatusRepository;
use Illuminate\Support\Facades\Response;

class EventSessionStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventSessionStatusController:list',
        'findById' => 'EventSessionStatusController:findById',
        'create' => 'EventSessionStatusController:create',
        'update' => 'EventSessionStatusController:update',
        'delete' => 'EventSessionStatusController:delete'
    ];

    public function __construct(private EventSessionStatusRepository $eventSessionStatusRepository) {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventSessionStatusRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventSessionStatusRepository->findById($id))
        );
    }


    public function create(CreateEventSessionStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventSessionStatusRepository->create($request->validated()))
        );
    }


    public function update(UpdateEventSessionStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventSessionStatusRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->eventSessionStatusRepository->delete($id);

        return Response::apiSuccess();
    }
}
