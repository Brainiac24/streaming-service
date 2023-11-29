<?php

namespace App\Http\Controllers;

use App\Constants\StatusCodes;
use App\Http\Requests\EventStatus\CreateEventStatusRequest;
use App\Http\Requests\EventStatus\UpdateEventStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\EventStatus\EventStatusRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class EventStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventStatusController:list',
        'findById' => 'EventStatusController:findById',
        'create' => 'EventStatusController:create',
        'update' => 'EventStatusController:update',
        'delete' => 'EventStatusController:delete'
    ];

    public function __construct(private EventStatusRepository $eventStatusRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventStatusRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventStatusRepository->findById($id))
        );
    }


    public function create(CreateEventStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventStatusRepository->create($request->validated()))
        );
    }


    public function update(UpdateEventStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventStatusRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->eventStatusRepository->delete($id);
        return Response::apiSuccess();
    }
}
