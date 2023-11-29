<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Constants\FareTypes;
use App\Constants\ImagePlaceholders;
use App\Exceptions\NotFoundException;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\EnterEventRequest;
use App\Http\Requests\Event\ProjectLinkEventRequest;
use App\Http\Requests\Event\RedirectEventUsersRequest;
use App\Http\Requests\Event\UpdateEventCoverImgRequest;
use App\Http\Requests\Event\UpdateEventLogoImgRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Event\EventItemResource;
use App\Http\Resources\Event\EventReceptionResource;
use App\Http\Resources\Event\UpcomingEventListResource;
use App\Http\Resources\EventDataCollectionTemplate\EventDataCollectionTemplateWithUserDataResource;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Event\EventService;
use App\Services\EventDataCollectionTemplate\EventDataCollectionTemplateService;
use Cache;
use DB;
use Response;

class EventController extends Controller
{

    static $actionPermissionMap = [
        'upcoming' => 'EventController:upcoming',
        'findById' => 'EventController:findById',
        'create' => 'EventController:create',
        'update' => 'EventController:update',
        'delete' => 'EventController:delete',
        'updateCoverImg' => 'EventController:updateCoverImg',
        'updateLogoImg' => 'EventController:updateLogoImg',
        'deleteCoverImg' => 'EventController:deleteCoverImg',
        'deleteLogoImg' => 'EventController:deleteLogoImg',
    ];

    public function __construct(private EventService $eventService, private EventDataCollectionTemplateService $eventDataCollectionTemplateService)
    {
        //
    }

    public function upcoming()
    {
        return Response::apiSuccess(
            new UpcomingEventListResource($this->eventService->upcoming())
        );
    }

    public function archive()
    {
        return Response::apiSuccess(
            new UpcomingEventListResource($this->eventService->archive())
        );
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new EventItemResource($this->eventService->eventRepository->findByIdForCurrentAuthedUserAndRequisite($id))
        );
    }

    public function create(CreateEventRequest $request)
    {
        DB::beginTransaction();
        try {


            $event = $this->eventService->create($request->validated());

            $result = Response::apiSuccess(
                new EventItemResource($event)
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

    public function update(UpdateEventRequest $request, $id)
    {
        $requestData = $request->validated();

        DB::beginTransaction();
        try {
            $event = $this->eventService->update($requestData, $id);

            if (isset($requestData['link'])) {
                CacheServiceFacade::forget(CacheKeys::eventIdByEventLinkKey($requestData['link']));
            }

            CacheServiceFacade::tags(CacheKeys::eventIdTag($id))
                ->flush();

            $result = Response::apiSuccess(
                new EventItemResource($event)
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
        $event = $this->eventService->updateCoverImg($request->validated(), $id);

        CacheServiceFacade::tags(CacheKeys::eventIdTag($id))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $event)
        );
    }
    public function updateLogoImg(UpdateEventLogoImgRequest $request, $id)
    {
        $event = $this->eventService->updateLogoImg($request->validated(), $id);

        CacheServiceFacade::tags(CacheKeys::eventIdTag($id))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $event)
        );
    }

    public function deleteCoverImg($id)
    {
        $event = $this->eventService->deleteCoverImg($id);

        CacheServiceFacade::tags(CacheKeys::eventIdTag($id))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $event)
        );
    }

    public function deleteLogoImg($id)
    {
        $event = $this->eventService->deleteLogoImg($id);

        CacheServiceFacade::tags(CacheKeys::eventIdTag($id))
            ->flush();

        return Response::apiSuccess(
            new BaseJsonResource(data: $event)
        );
    }

    public function redirect(RedirectEventUsersRequest $request, $id)
    {
        $this->eventService->redirect($id, $request->url);
        return Response::apiSuccess();
    }

    public function reception(ProjectLinkEventRequest $request, $link)
    {
        $requestData = $request->validated();

        if (
            isset($requestData['project_link']) &&
            !empty($requestData['project_link']) &&
            !$this->eventService->eventRepository->hasEventByLinkAndProjectLink($link, $requestData['project_link'])
        ) {
            throw new NotFoundException();
        }

        $eventId = $this->eventService->eventRepository->eventIdByLink($link);

        $reception = Cache::get(CacheKeys::receptionByEventIdKey($eventId));

        if (!$reception) {
            $reception = new EventReceptionResource($this->eventService->reception($eventId));

            $eventSessionIds = [];
            $streamIds = [];
            $eventDataCollectionTemplateIds = [];

            $projectId = $reception->data['project_id'];

            $eventDataCollectionTemplates = $this->eventDataCollectionTemplateService->getTemplateWithUserDataByEventId($reception->data['id']);
            $reception->data['event_data_collection_templates'] = [];
            if (!empty($eventDataCollectionTemplates)) {
                $eventDataCollectionTemplateResource = new EventDataCollectionTemplateWithUserDataResource($eventDataCollectionTemplates);
                $reception->data['event_data_collection_templates'] = $eventDataCollectionTemplateResource?->toArray()['data'] ?? [];
            }

            foreach ($reception->data['event_sessions'] as $eventSession) {
                if (
                    $eventSession['fare_id'] == FareTypes::PREMIUM &&
                    ($reception->data['logo_img_path'] == ImagePlaceholders::LOGO_PLACEHOLDER || $reception->data['logo_img_path'] == null)
                ) {
                    $reception->data['logo_img_path'] = $eventSession['logo_img_path'];
                }
                $eventSessionIds[] = $eventSession['id'];
                $streamIds[] = $eventSession['stream']['id'];
            }
            if (!empty($reception->data['event_data_collection_templates'])) {
                foreach ($reception->data['event_data_collection_templates'] as $item) {
                    $eventDataCollectionTemplateIds[] = $item['id'];
                }
            }

            CacheServiceFacade::tags([
                CacheKeys::projectIdTag($projectId),
                CacheKeys::eventIdTag($eventId),
                ...CacheKeys::setEventSessionIdTags($eventSessionIds),
                ...CacheKeys::setStreamIdTags($streamIds),
                ...CacheKeys::setEventDataCollectionTemplateIdTags($eventDataCollectionTemplateIds)
            ])
                ->set(
                    CacheKeys::receptionByEventIdKey($eventId),
                    $reception,
                    config('cache.ttl')
                );
        }

        //$event =  new EventReceptionResource($this->eventService->reception($link, $request->validated()));

        return Response::apiSuccess($reception);
    }

    public function enter(EnterEventRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $result = Response::apiSuccess(
                new BaseJsonResource(data: $this->eventService->enter($request->validated(), $id))
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
}
