<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Event\AdminEventListRequest;
use App\Http\Resources\EventSession\EventSessionAdminListResource;
use App\Http\Resources\Event\AdminEventListResource;
use App\Http\Resources\Event\AdminEventResource;
use App\Repositories\Event\EventRepository;
use App\Services\Event\EventService;
use Illuminate\Support\Facades\Response;

class AdminEventController extends Controller
{

    public function __construct(
        public EventRepository $eventRepository,
        public EventService $eventService,
    ) {
        //
    }

    public function getEventSessionList(){
        return Response::apiSuccess(
            new EventSessionAdminListResource($this->eventService->getAllForAdminEventSessionsList())
        );
    }

    public function getEventList(AdminEventListRequest $adminEventListRequest){
        return Response::apiSuccess(
            new AdminEventListResource($this->eventService->getAllForAdminEventsList($adminEventListRequest->validated()))
        );
    }

    public function getEvent($event_id){
        return Response::apiSuccess(
            new AdminEventResource($this->eventService->getForAdminEventById($event_id))
        );
    }


}

