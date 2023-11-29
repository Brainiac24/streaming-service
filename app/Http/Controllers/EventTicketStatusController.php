<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventTicketStatus\CreateEventTicketStatusRequest;
use App\Http\Requests\EventTicketStatus\UpdateEventTicketStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\EventTicketStatus\EventTicketStatusRepository;
use Illuminate\Support\Facades\Response;

class EventTicketStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventTicketStatusController:list',
        'findById' => 'EventTicketStatusController:findById',
        'create' => 'EventTicketStatusController:create',
        'update' => 'EventTicketStatusController:update',
        'delete' => 'EventTicketStatusController:delete'
    ];

    public function __construct(private EventTicketStatusRepository $eventTicketStatusRepository) {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventTicketStatusRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventTicketStatusRepository->findById($id))
        );
    }


    public function create(CreateEventTicketStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventTicketStatusRepository->create($request->validated()))
        );
    }


    public function update(UpdateEventTicketStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->eventTicketStatusRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->eventTicketStatusRepository->delete($id);

        return Response::apiSuccess();
    }
}
