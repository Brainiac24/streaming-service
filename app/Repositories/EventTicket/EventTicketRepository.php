<?php

namespace App\Repositories\EventTicket;

use App\Constants\CacheKeys;
use App\Constants\EventTicketStatuses;
use App\Constants\EventTicketTypes;
use App\Constants\OrderByTypes;
use App\Constants\Roles;
use App\Exceptions\ValidationException;
use App\Models\EventTicket;
use App\Repositories\BaseRepository;
use App\Repositories\RoleUser\RoleUserRepository;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;
use Auth;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Request;

class EventTicketRepository extends BaseRepository
{
    public function __construct(public EventTicket $eventTicket, public UserRepository $userRepository, public RoleUserRepository $roleUserRepository)
    {
        parent::__construct($eventTicket);
    }

    public function findById($id)
    {

        $eventTicket = CacheServiceFacade::get(CacheKeys::eventTicketIdKey($id));
        if (!$eventTicket) {
            $eventTicket = $this->eventTicket
                ->where('event_tickets.id', $id)
                ->firstOrFail([
                    'event_tickets.*'
                ]);

            CacheServiceFacade::tags([
                    CacheKeys::eventIdTag($eventTicket['event_id']),
                    CacheKeys::eventTicketIdTag($eventTicket['id'])
                ])
                ->set(
                    CacheKeys::eventTicketIdKey($id),
                    $eventTicket,
                    config('cache.ttl')
                );
        }

        return $eventTicket;

    }

    public function allForCurrentAuthedUser($eventId, $status = null, $ticketTypeId = EventTicketTypes::UNIQUE, $search = '', $isWithPagination = true)
    {

        $query = $this->eventTicket->currentAuthedUser($eventId);

        $query->leftJoin('users as client_users', 'client_users.id', '=', 'event_tickets.user_id');

        try {
            if (!empty($status)) {
                $status = strtoupper($status);
                $query->where('event_tickets.event_ticket_status_id', '=', constant(EventTicketStatuses::class . '::' . $status));
            }
        } catch (\Throwable $th) {
            //throw new ValidationException('Validation error: Event ticket status is not valid!');
        }


        $searchWordList = [];
        if (!empty($search)) {
            $searchWordList = preg_split("/[ |,]/", $search);
        }

        if (!empty($searchWordList)) {
            $query->where(function ($andQuery) use ($searchWordList) {
                foreach ($searchWordList as $searchWord) {
                    $andQuery
                        ->orWhere('client_users.email', 'like', "%{$searchWord}%")
                        ->orWhere('client_users.name', 'like', "%{$searchWord}%")
                        ->orWhere('client_users.lastname', 'like', "%{$searchWord}%")
                        ->orWhere('client_users.contact_email', 'like', "%{$searchWord}%")
                        ->orWhere('event_tickets.ticket', 'like', "%{$searchWord}%");
                }
            });
        }

        $query
            ->where('event_ticket_type_id', $ticketTypeId)
            ->groupBy('event_tickets.id');

        $columns = [
            'event_tickets.*',
            'client_users.name as user_name',
            'client_users.lastname as user_lastname',
            'client_users.is_verified as user_is_verified',
            'client_users.avatar_path as user_avatar_path',
            'client_users.email as user_email',
            'client_users.contact_email as user_contact_email'
        ];

        if ($isWithPagination) {
            $result = $query->withQueryFilters('asc', 'event_tickets.id')->paginate(perPage: Request::get('perPage', 15), columns: $columns);

            return $result;
        }

        $result = $query->withQueryFilters('asc', 'event_tickets.id')->get($columns);

        return $result;
    }
    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->eventTicket
            ->currentAuthedUserByAuthId()
            ->join('event_ticket_statuses', 'event_ticket_statuses.id', 'event_tickets.event_ticket_status_id')
            ->join('event_ticket_types', 'event_ticket_types.id', 'event_tickets.event_ticket_type_id')
            ->leftJoin('users as client_users', 'client_users.id', 'event_tickets.user_id')
            ->findOrFail($id, [
                'event_tickets.*',
                'event_ticket_statuses.name as event_ticket_status_name',
                'event_ticket_types.name as event_ticket_type_name',
                'client_users.name as user_name',
                'client_users.lastname as user_lastname',
                'client_users.is_verified as user_is_verified',
                'client_users.avatar_path as user_avatar_path',
                'client_users.email as user_email'
            ]);
    }

    public function getTicketDataCountsForCurrentAuthedUser($eventId)
    {
        return $this->eventTicket
            ->currentAuthedUser($eventId)
            ->join('event_ticket_statuses', 'event_ticket_statuses.id', 'event_tickets.event_ticket_status_id')
            ->join('event_ticket_types', 'event_ticket_types.id', 'event_tickets.event_ticket_type_id')
            ->where('event_id', $eventId)
            ->groupBy('event_ticket_type_id', 'event_ticket_status_id')
            ->get([
                DB::raw('count(' . DB::getTablePrefix() . 'event_tickets.id) as event_ticket_count'),
                'event_ticket_statuses.name as event_ticket_status_name',
                'event_ticket_types.name as event_ticket_type_name'
            ]);
    }

    public function getTicketsByTicketText($eventId, $tickets)
    {
        return $this->eventTicket
            ->currentAuthedUser($eventId)
            ->whereIn('ticket', $tickets)
            ->get(['event_tickets.*']);
    }

    public function getTicketsByEventId($eventId)
    {
        return $this->eventTicket
            ->currentAuthedUser($eventId)
            ->first(['event_tickets.*']);
    }

    public function getEventTicketForCurrentAuthedUser($eventTicketId)
    {
        return $this->eventTicket
            ->currentAuthedUserByAuthId()
            ->join('event_ticket_statuses', 'event_ticket_statuses.id', 'event_tickets.event_ticket_status_id')
            ->join('event_ticket_types', 'event_ticket_types.id', 'event_tickets.event_ticket_type_id')
            ->leftJoin('users as client_users', 'client_users.id', 'event_tickets.user_id')
            ->where('event_tickets.id', $eventTicketId)
            ->firstOrFail([
                'event_tickets.*',
                'client_users.name as user_name',
                'client_users.lastname as user_lastname',
                'client_users.is_verified as user_is_verified',
                'client_users.avatar_path as user_avatar_path',
                'client_users.email as user_email',
                'events.access_group_id'
            ]);
    }

    public function getEventTicketById($eventTicketId)
    {
        return $this->eventTicket
            ->join('events',  'events.id', '=', 'event_tickets.event_id')
            ->join('projects', 'projects.id', '=', 'events.project_id')
            ->join('event_ticket_statuses', 'event_ticket_statuses.id', 'event_tickets.event_ticket_status_id')
            ->join('event_ticket_types', 'event_ticket_types.id', 'event_tickets.event_ticket_type_id')
            ->leftJoin('users as client_users', 'client_users.id', 'event_tickets.user_id')
            ->where('event_tickets.id', $eventTicketId)
            ->firstOrFail([
                'event_tickets.*',
                'client_users.name as user_name',
                'client_users.lastname as user_lastname',
                'client_users.is_verified as user_is_verified',
                'client_users.avatar_path as user_avatar_path',
                'client_users.email as user_email',
                'events.access_group_id'
            ]);
    }


    public function updateManyForCurrentAuthedUser(array $eventTicketIds, array $data)
    {
        if (empty($eventTicketIds)) {
            return false;
        }

        return $this->eventTicket
            ->currentAuthedUserByAuthId()
            ->join('event_ticket_statuses', 'event_ticket_statuses.id', 'event_tickets.event_ticket_status_id')
            ->join('event_ticket_types', 'event_ticket_types.id', 'event_tickets.event_ticket_type_id')
            ->leftJoin('users as client_users', 'client_users.id', 'event_tickets.user_id')
            ->whereIn('event_tickets.id', $eventTicketIds)
            ->update($data);
    }

    public function updateForCurrentAuthedUser($id, $data, $eventTicketTypeId = null)
    {

        $query = $this->eventTicket
            ->currentAuthedUserByAuthId()
            ->where('event_tickets.id', $id);

        if ($eventTicketTypeId) {
            $query->where('event_tickets.event_ticket_type_id', $eventTicketTypeId);
        }

        $eventTicket = $query->firstOrFail(['event_tickets.*', 'events.access_group_id']);

        $eventTicket->update($data);

        CacheServiceFacade::tags([
            CacheKeys::eventTicketIdTag($id),
            CacheKeys::eventIdTag($eventTicket['event_id'])
        ])
            ->flush();

        $this->checkTicketAndAttachOrDetachRoleMember($eventTicket);

        return $eventTicket;
    }

    public function delete($id)
    {
        if ($this->model->currentAuthedUserByAuthId()->where('event_tickets.id', $id)->delete() == 0) {
            throw new ModelNotFoundException();
        }
        CacheServiceFacade::tags([
            CacheKeys::eventTicketIdTag($id)
        ])
            ->flush();
        return true;
    }

    public function findMultiByTicket($ticket, $eventId)
    {
        return $this->eventTicket
            ->where('event_id', $eventId)
            ->where('ticket', $ticket)
            ->where('event_ticket_status_id', EventTicketStatuses::ACTIVE)
            ->where('event_ticket_type_id', EventTicketTypes::MULTI)
            ->first();
    }

    public function findUniqueByTicket($ticket, $eventId)
    {
        return $this->eventTicket
            ->where('event_id', $eventId)
            ->where('ticket', $ticket)
            ->where('event_ticket_type_id', EventTicketTypes::UNIQUE)
            ->where(function ($query) {
                $query
                    ->where('event_ticket_status_id', EventTicketStatuses::ACTIVE)
                    ->orWhere('event_ticket_status_id', EventTicketStatuses::USED);
            })
            ->first();
    }

    public function getUsersByEventIdAndUniqueType($eventId)
    {
        return $this->eventTicket
            ->where('event_id', $eventId)
            ->where('event_ticket_type_id', EventTicketTypes::UNIQUE)
            ->get();
    }

    public function checkTicketAndAttachOrDetachRoleMember($eventTicket)
    {
        if ($eventTicket['event_ticket_status_id'] == EventTicketStatuses::INACTIVE || $eventTicket['event_ticket_status_id'] == EventTicketStatuses::BANNED) {
            $this->roleUserRepository->deleteByRoleIdAndEventTicketId(Roles::MEMBER, $eventTicket['id']);
        }

        if (
            $eventTicket['event_ticket_type_id'] == EventTicketTypes::UNIQUE &&
            $eventTicket['user_id'] &&
            ($eventTicket['event_ticket_status_id'] == EventTicketStatuses::ACTIVE || $eventTicket['event_ticket_status_id'] == EventTicketStatuses::USED)
        ) {
            $user = $this->userRepository->findById($eventTicket['user_id']);
            $user->attachRoleAndEventTicketToAccessGroup(Roles::MEMBER, $eventTicket['id'], $eventTicket['access_group_id']);
        }
    }
}
