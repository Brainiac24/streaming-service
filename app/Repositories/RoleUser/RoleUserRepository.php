<?php

namespace App\Repositories\RoleUser;

use App\Constants\CacheKeys;
use App\Models\RoleUser;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class RoleUserRepository extends BaseRepository
{
    public function __construct(public RoleUser $roleUser)
    {
        parent::__construct($roleUser);
    }

    public function deleteByRoleIdAndEventTicketId($roleId, $eventTicketId)
    {
        $roleUser = $this->roleUser
            ->where('role_id', '=', $roleId)
            ->where('event_ticket_id', '=', $eventTicketId)
            ->get();

        $result = $this->roleUser
            ->where('role_id', '=', $roleId)
            ->where('event_ticket_id', '=', $eventTicketId)
            ->delete();

        if ($roleUser->isNotEmpty()) {
            CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($roleUser[0]['access_group_id']))
                ->flush();
        }

        return $result;
    }


    public function deleteByRoleIdAndEventTicketIsNull($roleId, $accessGroupId)
    {
        $roleUser = $this->roleUser
            ->where('role_id', '=', $roleId)
            ->where('access_group_id', '=', $accessGroupId)
            ->whereNull('event_ticket_id')
            ->delete();


        CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($accessGroupId))
            ->flush();


        return $roleUser;
    }

    public function deleteByRoleIdAndEventTicketIsNullAndTicketTypeId($roleId, $accessGroupId, $ticketTypeId)
    {
        $roleUser = $this->roleUser
            ->leftJoin('event_tickets', 'event_tickets.id', '=', 'role_user.event_ticket_id')
            ->where('role_id', '=', $roleId)
            ->where('access_group_id', '=', $accessGroupId)
            ->where(function ($query) use ($ticketTypeId) {
                $query
                    ->whereNull('role_user.event_ticket_id')
                    ->orWhere('event_tickets.event_ticket_type_id', '=', $ticketTypeId);
            })
            ->delete();


        CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($accessGroupId))
            ->flush();


        return $roleUser;
    }


    public function deleteByRoleIdAndAccessGroupId($roleId, $accessGroupId)
    {
        $roleUser = $this->roleUser
            ->where('role_id', '=', $roleId)
            ->where('access_group_id', '=', $accessGroupId)
            ->delete();


        CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($accessGroupId))
            ->flush();


        return $roleUser;
    }
}
