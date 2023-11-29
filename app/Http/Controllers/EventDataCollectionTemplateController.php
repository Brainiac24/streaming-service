<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Http\Requests\EventDataCollectionTemplate\CreateEventDataCollectionTemplateRequest;
use App\Http\Requests\EventDataCollectionTemplate\UpdateEventDataCollectionTemplateRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Services\Cache\CacheServiceFacade;
use Illuminate\Support\Facades\Response;

class EventDataCollectionTemplateController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventDataCollectionTemplateController:list',
        'findById' => 'EventDataCollectionTemplateController:findById',
        'create' => 'EventDataCollectionTemplateController:create',
        'update' => 'EventDataCollectionTemplateController:update',
        'delete' => 'EventDataCollectionTemplateController:delete'
    ];

    public function __construct(private EventDataCollectionTemplateRepository $eventDataCollectionTemplateRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventDataCollectionTemplateRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventDataCollectionTemplateRepository->findById($id))
        );
    }


    public function create(CreateEventDataCollectionTemplateRequest $request)
    {
        $eventDataCollectionTemplate = $this->eventDataCollectionTemplateRepository->create($request->validated());

        CacheServiceFacade::tags(CacheKeys::eventIdTag($eventDataCollectionTemplate['event_id']))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $eventDataCollectionTemplate)
        );
    }


    public function update(UpdateEventDataCollectionTemplateRequest $request, $id)
    {
        $eventDataCollectionTemplate = $this->eventDataCollectionTemplateRepository->update($request->validated(), $id);

        CacheServiceFacade::tags(CacheKeys::eventIdTag($eventDataCollectionTemplate['event_id']))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $eventDataCollectionTemplate)
        );
    }


    public function delete($id)
    {
        $eventDataCollectionTemplate = $this->eventDataCollectionTemplateRepository->findById($id);

        $this->eventDataCollectionTemplateRepository->delete($id);

        CacheServiceFacade::tags(CacheKeys::eventIdTag($eventDataCollectionTemplate['event_id']))
            ->flush();

        return Response::apiSuccess();
    }

    public function findTemplatesByEventId($eventId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventDataCollectionTemplateRepository->getTemplatesByEventId($eventId))
        );
    }
}
