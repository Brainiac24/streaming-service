<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Http\Requests\Event\ProjectLinkEventRequest;
use App\Http\Requests\Event\UpdateEventLogoImgRequest;
use App\Http\Requests\EventSession\CreateEventSessionRequest;
use App\Http\Requests\EventSession\UpdateEventSessionRequest;
use App\Http\Requests\EventSession\UpdateFareForEventSessionRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\EventSession\EventSessionItemForStreamViewByKeyResource;
use App\Http\Resources\EventSession\EventSessionItemForStreamViewResource;
use App\Http\Resources\EventSession\EventSessionItemResource;
use App\Http\Resources\EventSession\EventSessionListResource;
use App\Repositories\Fare\FareRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\EventSession\EventSessionService;
use App\Services\EventTicket\EventTicketService;
use App\Services\WebSocket\WebSocketService;
use DB;
use Response;

class EventSessionController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventSessionController:list',
        'findById' => 'EventSessionController:findById',
        'create' => 'EventSessionController:create',
        'update' => 'EventSessionController:update',
        'delete' => 'EventSessionController:delete'
    ];

    public function __construct(
        public EventSessionService $eventSessionService,
        public FareRepository $fareRepository,
        public WebSocketService $webSocketService,
        public EventTicketService $eventTicketService
    ) {
        //
    }

    public function list($eventId)
    {
        return Response::apiSuccess(
            new EventSessionListResource($this->eventSessionService->list($eventId))
        );
    }


    public function findById($id)
    {
        $eventSession = $this->eventSessionService->findById($id);
        $accessGroupId = $this->eventSessionService->eventSessionRepository->accessGroupIdByEventSessionId($id);
        return Response::apiSuccess(
            new EventSessionItemResource($eventSession, $accessGroupId, $this->fareRepository->getFirstExtraFare())
        );
    }

    public function findByKeyForStreamRoom($key)
    {
        $eventSession = $this->eventSessionService->findByKeyWithStream($key);
        $accessGroupId = $this->eventSessionService->eventSessionRepository->accessGroupIdByEventSessionId($eventSession['event_session_id']);

        return Response::apiSuccess(
            new EventSessionItemForStreamViewByKeyResource($eventSession, $accessGroupId)
        );
    }

    public function findByIdForStreamRoom(ProjectLinkEventRequest $request, $id)
    {
        $eventSession = $this->eventSessionService->findByIdWithStream($id, $request->validated());

        if ($eventSession && $eventSession['is_unique_ticket_enabled']) {
            $this->eventSessionService->publishKickCurrentUserFromOtherSession($eventSession['id']);
        }

        $accessGroupId = $this->eventSessionService->eventSessionRepository->accessGroupIdByEventSessionId($eventSession['id']);
        return Response::apiSuccess(
            new EventSessionItemForStreamViewResource($eventSession, $accessGroupId)
        );
    }

    public function findByCodeForStreamRoom(ProjectLinkEventRequest $request, $eventLink, $code)
    {
        $eventSession = $this->eventSessionService->findByEventLinkAndSessionCodeWithStream($eventLink, $code, $request->validated());

        if ($eventSession && $eventSession['is_unique_ticket_enabled']) {
            $this->eventSessionService->publishKickCurrentUserFromOtherSession($eventSession['id']);
        }

        $accessGroupId = $this->eventSessionService->eventSessionRepository->accessGroupIdByEventSessionId($eventSession['id']);
        return Response::apiSuccess(
            new EventSessionItemForStreamViewResource($eventSession, $accessGroupId)
        );
    }

    public function create(CreateEventSessionRequest $request, $eventId)
    {

        DB::beginTransaction();
        try {
            $eventSession = $this->eventSessionService->createEventSession($eventId, $request->validated());
            $accessGroupId = $this->eventSessionService->eventSessionRepository->accessGroupIdByEventSessionId($eventSession['id']);
            CacheServiceFacade::tags([
                CacheKeys::eventSessionIdTag($eventSession['id']),
                CacheKeys::eventIdTag($eventId)
            ])->flush();
            $result = Response::apiSuccess(
                new EventSessionItemResource($eventSession, $accessGroupId)
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


    public function update(UpdateEventSessionRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $eventSession = $this->eventSessionService->updateEventSession($id, $request->validated());
            $accessGroupId = $this->eventSessionService->eventSessionRepository->accessGroupIdByEventSessionId($eventSession['id']);
            CacheServiceFacade::tags([
                CacheKeys::eventSessionIdTag($eventSession['id']),
                CacheKeys::eventIdTag($eventSession['event_id'])
            ])
                ->flush();
            $result = Response::apiSuccess(
                new EventSessionItemResource($eventSession, $accessGroupId)
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

    public function upgradeFare(UpdateFareForEventSessionRequest $request, $id)
    {
        $eventSession = $this->eventSessionService->upgradeFare($id, $request->validated());
        $accessGroupId = $this->eventSessionService->eventSessionRepository->accessGroupIdByEventSessionId($eventSession['id']);
        CacheServiceFacade::tags([
            CacheKeys::eventSessionIdTag($eventSession['id']),
            CacheKeys::eventIdTag($eventSession['event_id'])
        ])
            ->flush();
        return Response::apiSuccess(
            new EventSessionItemResource($eventSession, $accessGroupId)
        );
    }

    public function updateLogoImg(UpdateEventLogoImgRequest $request, $id)
    {
        $eventSession = $this->eventSessionService->updateLogoImg($request->validated(), $id);
        CacheServiceFacade::tags(CacheKeys::eventSessionIdTag($id))
            ->flush();
        return Response::apiSuccess(
            new BaseJsonResource(data: $eventSession)
        );
    }

    public function deleteLogoImg($id)
    {
        $this->eventSessionService->deleteLogoImg($id);
        CacheServiceFacade::tags(CacheKeys::eventSessionIdTag($id))
            ->flush();
        return Response::apiSuccess();
    }

    public function delete($id)
    {
        //
    }
}
