<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Http\Requests\EventDataCollection\CreateEventDataCollectionRequest;
use App\Http\Requests\EventDataCollection\UpdateEventDataCollectionRequest;
use App\Http\Resources\BaseJsonResource;
use App\Services\Cache\CacheServiceFacade;
use App\Services\EventDataCollection\EventDataCollectionService;
use Response;

class EventDataCollectionController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventDataCollectionController:list',
        'findById' => 'EventDataCollectionController:findById',
        'create' => 'EventDataCollectionController:create',
        'update' => 'EventDataCollectionController:update',
        'delete' => 'EventDataCollectionController:delete'
    ];

    public function __construct(private EventDataCollectionService $eventDataCollectionService)
    {
        //
    }

    public function listTemplate()
    {
        //
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventDataCollectionService->eventDataCollectionRepository->findById($id))
        );
    }

    public function create(CreateEventDataCollectionRequest $request)
    {
        $eventDataCollection = $this->eventDataCollectionService->create($request->validated());

        CacheServiceFacade::tags(CacheKeys::eventIdTag($eventDataCollection['event_id']))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $eventDataCollection)
        );
    }

    public function update(UpdateEventDataCollectionRequest $request, $id)
    {
        $eventDataCollection = $this->eventDataCollectionService->update($request->validated(), $id);

        CacheServiceFacade::tags(CacheKeys::eventIdTag($eventDataCollection['event_id']))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $eventDataCollection)
        );
    }

    public function delete($id)
    {
        //
    }
}
