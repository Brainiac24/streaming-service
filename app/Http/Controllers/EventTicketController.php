<?php

namespace App\Http\Controllers;

use App\Constants\EventTicketTypes;
use App\Http\Requests\EventTicket\AttachEventTicketToUserRequest;
use App\Http\Requests\EventTicket\BanEventTicketsRequest;
use App\Http\Requests\EventTicket\EventTicketListWithPaginationRequest;
use App\Http\Requests\EventTicket\ListByTicketsFileRequest;
use App\Http\Requests\EventTicket\ListByTicketsTextRequest;
use App\Http\Requests\EventTicket\UpdateEventMultiTicketRequest;
use App\Http\Requests\EventTicket\UpdateEventUniqueTicketRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\EventTicket\EventTicketItemResource;
use App\Http\Resources\EventTicket\EventTicketListResource;
use App\Services\EventTicket\EventTicketService;
use Illuminate\Support\Facades\Storage;
use Response;

class EventTicketController extends Controller
{

    static $actionPermissionMap = [
        'generateUniqueTickets' => 'EventTicketController:generateUniqueTickets',
        'generateMultiTickets' => 'EventTicketController:generateMultiTickets',
        'listUniqueWithPagination' => 'EventTicketController:listUniqueWithPagination',
        'listMultiWithPagination' => 'EventTicketController:listMultiWithPagination',
        'listByEventId' => 'EventTicketController:listByEventId',
        'attachTicketToUser' => 'EventTicketController:attachTicketToUser',
        'unbindTicketToUser' => 'EventTicketController:unbindTicketToUser',
        'banTicket' => 'EventTicketController:banTicket',
        'unbanTicket' => 'EventTicketController:unbanTicket'
    ];

    public function __construct(public EventTicketService $eventTicketService)
    {
        //
    }

    public function findByIdForCurrentAuthedUser($id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->eventTicketRepository->findByIdForCurrentAuthedUser($id)
            )
        );
    }

    public function generateUniqueTickets($eventId, $count = 1)
    {
        return Response::apiSuccess(
            new EventTicketListResource(
                data: $this->eventTicketService->generate($eventId, $count, EventTicketTypes::UNIQUE, true)
            )
        );
    }

    public function generateMultiTickets($eventId, $count = 1)
    {
        return Response::apiSuccess(
            new EventTicketListResource(
                data: $this->eventTicketService->generate($eventId, $count, EventTicketTypes::MULTI, true)
            )
        );
    }
    public function listByTicketsText(ListByTicketsTextRequest $request, $eventId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(
                data: $this->eventTicketService->listByTicketsText($eventId, $request->validated())
            )
        );
    }

    public function listByTicketsFile(ListByTicketsFileRequest $request, $eventId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(
                data: $this->eventTicketService->listByTicketsFile($eventId, $request->validated())
            )
        );
    }

    public function listUniqueWithPagination(EventTicketListWithPaginationRequest $request, $eventId, $status = null)
    {

        return Response::apiSuccess(
            new EventTicketListResource(
                data: $this->eventTicketService->listUniqueWithPagination($eventId, $status, $request->validated())
            )
        );
    }

    public function listMultiWithPagination(EventTicketListWithPaginationRequest $request, $eventId, $status = null)
    {
        return Response::apiSuccess(
            new EventTicketListResource(
                data: $this->eventTicketService->listMultiWithPagination($eventId, $status, $request->validated())
            )
        );
    }

    public function listByEventId($eventId)
    {
        return Response::apiSuccess(
            new EventTicketListResource(
                data: $this->eventTicketService->eventTicketRepository->allForCurrentAuthedUser($eventId)
            )
        );
    }

    public function updateMultiTicket(UpdateEventMultiTicketRequest $request, $id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->updateMultiTicket($id, $request->validated())
            )
        );
    }

    public function updateUniqueTicket(UpdateEventUniqueTicketRequest $request, $id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->updateUniqueTicket($id, $request->validated())
            )
        );
    }

    public function attachTicketToUserIfUserExist(AttachEventTicketToUserRequest $request, $id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->attachTicketToUser($id, strtolower($request->email), true)
            )
        );
    }

    public function attachTicketToUser(AttachEventTicketToUserRequest $request, $id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->attachTicketToUser($id, strtolower($request->email))
            )
        );
    }

    public function detachTicketToUser($id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->detachTicketToUser($id)
            )
        );
    }


    public function banTicket($id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->banEventTicket($id)
            )
        );
    }

    public function unbanTicket($id)
    {
        return Response::apiSuccess(
            new EventTicketItemResource(
                data: $this->eventTicketService->unbanEventTicket($id)
            )
        );
    }


    public function banTickets(BanEventTicketsRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(
                data: $this->eventTicketService->banEventTickets($request->ids)
            )
        );
    }

    public function unbanTickets(BanEventTicketsRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(
                data: $this->eventTicketService->unbanEventTickets($request->ids)
            )
        );
    }

    public function exportTickets(EventTicketListWithPaginationRequest $request, $eventId, $status = null)
    {
        return $this->eventTicketService->exportListUnique($eventId, $status, $request->validated());

    }

    public function downloadTickets(EventTicketListWithPaginationRequest $request, $eventId, $status = null)
    {
        return $this->eventTicketService->downloadListUnique($eventId, $status, $request->validated());
    }

    public function delete($id)
    {
        $this->eventTicketService->eventTicketRepository->delete($id);
        return Response::apiSuccess();
    }

    public function getTicketsData($eventId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(
                data: $this->eventTicketService->getTicketsData($eventId)
            )
        );
    }
}
