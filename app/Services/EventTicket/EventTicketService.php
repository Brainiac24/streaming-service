<?php


namespace App\Services\EventTicket;

use App\Constants\CacheKeys;
use App\Constants\EventTicketStatuses;
use App\Constants\EventTicketTypes;
use App\Constants\Roles;
use App\Constants\StatusCodes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use App\Exceptions\WrongCredentialException;
use App\Http\Resources\BaseJsonResource;
use App\Jobs\EventTicketExportJob;
use App\Models\EventTicket;
use App\Repositories\EventTicket\EventTicketRepository;
use App\Repositories\RoleUser\RoleUserRepository;
use App\Repositories\User\UserRepository;
use App\Services\EventAccess\EventAccessService;
use App\Services\Helper\XlsxExportHelperService;
use App\Services\WebSocket\WebSocketService;
use Auth;
use Cache;
use Carbon\Carbon;
use Response;
use Storage;
use Str;

class EventTicketService
{
    public function __construct(
        public EventTicketRepository $eventTicketRepository,
        public UserRepository $userRepository,
        public XlsxExportHelperService $xlsxExportHelperService,
        public RoleUserRepository $roleUserRepository,
        public EventAccessService $eventAccessService,
        public WebSocketService $webSocketService
    ) {
    }

    public function generate($eventId, $count, $eventTicketTypeId = EventTicketTypes::UNIQUE, $retrieveInserted = false)
    {
        $data = [];

        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'event_id' => $eventId,
                'ticket' => $this->generateRandomPass(),
                'event_ticket_status_id' => EventTicketStatuses::ACTIVE,
                'event_ticket_type_id' => $eventTicketTypeId,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
        }

        if ($retrieveInserted) {
            return $this->eventTicketRepository->insertAndRetrieve($data);
        }

        return $this->eventTicketRepository->insert($data);
    }

    public function getTicketsData($eventId)
    {
        return $this->eventTicketRepository->getTicketDataCountsForCurrentAuthedUser($eventId);
    }

    public function listByTicketsText($eventId, $requestData)
    {
        $delimiters = '/[,|.|;| ]/';
        $ticketsText = preg_split($delimiters, $requestData['tickets'], 0, PREG_SPLIT_NO_EMPTY);
        $tickets = $this->eventTicketRepository->getTicketsByTicketText($eventId, $ticketsText);

        if ($tickets) {
            return $tickets->pluck('id');
        }
        return [];
    }

    public function listByTicketsFile($eventId, $requestData)
    {
        $file = $requestData['file'];
        $ext = $file->getClientOriginalExtension();
        if (!in_array($ext, ["xlsx", "csv"])) return [];

        if ($ext === 'xlsx') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        if ($ext === 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        }
        $spreadsheet = $reader->load($file->getPathName());

        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();
        $words = [];
        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                if ($value && !in_array($value, $words)) $words[] = trim($value);
            }
        }

        $tickets = $this->eventTicketRepository->getTicketsByTicketText($eventId, $words);

        if ($tickets) {
            return $tickets->pluck('id');
        }
        return [];
    }

    public function listUniqueWithPagination($eventId, $status, $eventTicketData)
    {
        return $this->eventTicketRepository->allForCurrentAuthedUser(
            $eventId,
            $status,
            EventTicketTypes::UNIQUE,
            $eventTicketData['search'] ?? null,
            true
        );
    }

    public function listMultiWithPagination($eventId, $status, $eventTicketData)
    {
        return $this->eventTicketRepository->allForCurrentAuthedUser(
            $eventId,
            $status,
            EventTicketTypes::MULTI,
            $eventTicketData['search'] ?? null,
            true
        );
    }

    public function exportListUnique($eventId, $status, $eventTicketData)
    {
        if (!$this->eventTicketRepository->getTicketsByEventId($eventId))
        {
            throw new NotFoundException();
        } 

        $ticketListFile = Cache::get(CacheKeys::EventTicketsByEventIdKey($eventId));
        $link = url('api/v1/events/' . $eventId.'/tickets/unique/export/');

        if ($status) {
            $link = url('api/v1/events/' . $eventId.'/tickets/unique/export/'.$status);
            $ticketListFile =Cache::get(CacheKeys::eventTicketsByEventIdAndStatusKey($eventId,$status));
        }
        if (isset($eventTicketData['search']) && !empty($eventTicketData['search'])) {
            $link = url('api/v1/events/' . $eventId.'/tickets/unique/export/'.$status.'?search='.$eventTicketData['search']);
            $ticketListFile =Cache::get(CacheKeys::EventTicketsByEventIdAndStatusWithSearchKey($eventId,$status,$eventTicketData['search']));
        }
        if (!$ticketListFile) {
            EventTicketExportJob::dispatch($eventId,$status,$eventTicketData);
            return Response::apiSuccess(
                new BaseJsonResource(
                    code: StatusCodes::IN_PROCESS,
                    message: __('In process'),
                )
            );
        }

        return Response::apiSuccess(
            new BaseJsonResource(
                code: StatusCodes::SUCCESS,
                message: __('Success'),
                data: [
                    'link' => $link
                ]
            )
        );
    }

    public function downloadListUnique($eventId, $status, $eventTicketData)
    {

        if (!$this->eventTicketRepository->getTicketsByEventId($eventId)) {
            throw new NotFoundException();
        }

        $ticketListFile = Cache::get(CacheKeys::EventTicketsByEventIdKey($eventId));

        if ($status) {
            $ticketListFile =Cache::get(CacheKeys::EventTicketsByEventIdAndStatusKey($eventId,$status));
        }
        if ($eventTicketData['search'] ?? false) {
            $ticketListFile =Cache::get(CacheKeys::EventTicketsByEventIdAndStatusWithSearchKey($eventId,$status,$eventTicketData['search']));
        }
        if (!$ticketListFile) {
            return Response::apiError(
                new BaseJsonResource(
                    code: StatusCodes::NOT_FOUND_ERROR,
                    message: __('Not found!')
                ),
                403
            );
        }

        return Storage::disk("local")->download('local/xlsx/' . $ticketListFile, headers: [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Length' => null,
        ]);
    }

    public function updateMultiTicket($id, $eventTicketData)
    {
        return $this->eventTicketRepository->updateForCurrentAuthedUser($id, $eventTicketData, EventTicketTypes::MULTI);
    }

    public function updateUniqueTicket($id, $eventTicketData)
    {
        return $this->eventTicketRepository->updateForCurrentAuthedUser($id, $eventTicketData, EventTicketTypes::UNIQUE);
    }

    public function attachTicketToUser($eventTicketId, $email, $checkIfUserExist = false)
    {
        $eventTicket = $this->eventTicketRepository->getEventTicketForCurrentAuthedUser($eventTicketId);

        $user = $this->userRepository->findByEmail($email);

        if (!$user && empty($user)) {
            if ($checkIfUserExist) {
                throw new WrongCredentialException(__('Wrong credentials error: User by provided email is not exist!'));
            } else {
                $user = $this->userRepository->createByEmail($email);
            }
        }

        $eventTicket->user_id = $user->id;
        $eventTicket->event_ticket_status_id = EventTicketStatuses::USED;

        if (!$eventTicket->save()) {
            throw new UnknownException('Unknown error: Could not bind event ticket to user!');
        }

        $this->eventTicketRepository->checkTicketAndAttachOrDetachRoleMember($eventTicket);

        return $this->eventTicketRepository->getEventTicketForCurrentAuthedUser($eventTicketId);;
    }

    public function detachTicketToUser($eventTicketId)
    {
        $eventTicket = $this->eventTicketRepository->getEventTicketForCurrentAuthedUser($eventTicketId);

        $eventTicket->user_id = null;
        $eventTicket->event_ticket_status_id = EventTicketStatuses::ACTIVE;

        if (!$eventTicket->save()) {
            throw new UnknownException('Unknown error: Could not unbind event ticket to user!');
        }

        $this->roleUserRepository->deleteByRoleIdAndEventTicketId(Roles::MEMBER, $eventTicket['id']);

        return $this->eventTicketRepository->getEventTicketForCurrentAuthedUser($eventTicketId);
    }

    public function detachTicket($eventTicket)
    {
        $eventTicket->user_id = null;
        $eventTicket->event_ticket_status_id = EventTicketStatuses::ACTIVE;

        if (!$eventTicket->save()) {
            throw new UnknownException('Unknown error: Could not unbind event ticket to user!');
        }

        $this->roleUserRepository->deleteByRoleIdAndEventTicketId(Roles::MEMBER, $eventTicket['id']);

        return $this->eventTicketRepository->getEventTicketById($eventTicket['id']);
    }

    public function banEventTicket($eventTicketId)
    {
        $eventTicket = $this->eventTicketRepository->getEventTicketForCurrentAuthedUser($eventTicketId);

        $eventTicket->event_ticket_status_id = EventTicketStatuses::BANNED;

        if (!$eventTicket->save()) {
            throw new UnknownException('Unknown error: Could not ban event ticket!');
        }

        $this->eventTicketRepository->checkTicketAndAttachOrDetachRoleMember($eventTicket);

        return $eventTicket;
    }

    public function unbanEventTicket($eventTicketId)
    {
        $eventTicket = $this->eventTicketRepository->getEventTicketForCurrentAuthedUser($eventTicketId);

        $eventTicket->event_ticket_status_id = EventTicketStatuses::ACTIVE;

        if (!$eventTicket->save()) {
            throw new UnknownException('Unknown error: Could not unban event ticket!');
        }

        $this->eventTicketRepository->checkTicketAndAttachOrDetachRoleMember($eventTicket);

        return $eventTicket;
    }

    public function banEventTickets($eventTicketIds)
    {
        $eventTicket = $this->eventTicketRepository->updateManyForCurrentAuthedUser(
            $eventTicketIds,
            ['event_tickets.event_ticket_status_id' => EventTicketStatuses::BANNED]
        );

        if (!$eventTicket) {
            throw new UnknownException('Unknown error: Could not ban event tickets!');
        }

        return $eventTicket;
    }

    public function unbanEventTickets($eventTicketIds)
    {
        $eventTicket = $this->eventTicketRepository->updateManyForCurrentAuthedUser(
            $eventTicketIds,
            ['event_tickets.event_ticket_status_id' => EventTicketStatuses::ACTIVE]
        );

        if (!$eventTicket) {
            throw new UnknownException('Unknown error: Could not unban event tickets!');
        }

        return $eventTicket;
    }

    public function checkTicket($isCheckEnabled, $requestData, $event, $isMultiTicket = true)
    {
        if (!$isCheckEnabled) {
            return true;
        }

        if (!isset($requestData['ticket'])) {
            throw new ValidationException(__('Validation error: Ticket is required!'));
        }

        $ticket = $requestData['ticket'];

        if ($isMultiTicket) {
            $eventTicket = $this->eventTicketRepository->findMultiByTicket($ticket, $event['id']);
            if ($eventTicket) {
                $this->eventAccessService->attachRoleAndEventTicketToAccessGroup(Auth::user(), $event, Roles::MEMBER, $eventTicket['id']);
            }

            if (!$eventTicket) {
                return false;
            }

            return $eventTicket;
        } else {
            $eventTicket = $this->eventTicketRepository->findUniqueByTicket($ticket, $event['id']);

            if ($eventTicket && $eventTicket['user_id'] != Auth::id() && $eventTicket['event_ticket_status_id'] == EventTicketStatuses::USED) {
                if (!Auth::user()->email) {

                    $user = $this->userRepository->findById($eventTicket['user_id']);

                    if (!$user['email']) {
                        $this->detachTicket($eventTicket);

                        $socketData = new BaseJsonResource(
                            data: [
                                'event_id' => $event['id'],
                            ],
                            scope: WebSocketScopes::EVENT,
                            mutation: WebSocketMutations::SOCK_REMOVE_MEMBER_ROLE
                        );

                        $this->webSocketService->publish($user['channel'], $socketData);
                    }
                }
            }

            if (
                $eventTicket &&
                ($eventTicket['event_ticket_status_id'] == EventTicketStatuses::ACTIVE ||
                    ($eventTicket['user_id'] == Auth::id() && $eventTicket['event_ticket_status_id'] == EventTicketStatuses::USED)
                )
            ) {
                if ($eventTicket['event_ticket_status_id'] == EventTicketStatuses::ACTIVE) {
                    $eventTicket->user_id = Auth::id();
                    $eventTicket->event_ticket_status_id = EventTicketStatuses::USED;
                    $eventTicket->save();
                }

                $this->eventAccessService->attachRoleAndEventTicketToAccessGroup(Auth::user(), $event, Roles::MEMBER, $eventTicket['id']);

                return $eventTicket;
            }
            return false;
        }
    }

    public function generatePaidTicket(int $eventId, int $userId, float $price): EventTicket
    {
        $data = [
            'event_id' => $eventId,
            'user_id' => $userId,
            'ticket' => $this->generateRandomPass(),
            'event_ticket_status_id' => EventTicketStatuses::ACTIVE,
            'event_ticket_type_id' => EventTicketTypes::UNIQUE,
            'price' => $price
        ];

        return $this->eventTicketRepository->create($data);
    }

    private function generateRandomPass(): string
    {
        return Str::of(Str::random(8))
            ->replace('l', rand(1, 9))->replace('I', rand(1, 9))
            ->replace('0', rand(1, 9))->replace('O', rand(1, 9));
    }
}
