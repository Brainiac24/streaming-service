<?php

namespace App\Services\EventAccess;

use App\Constants\CacheKeys;
use App\Exceptions\WrongCredentialException;
use App\Repositories\AccessGroup\AccessGroupRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;

class EventAccessService
{
    public function __construct(
        public UserRepository $userRepository,
        public AccessGroupRepository $accessGroupRepository,
        public EventSessionRepository $eventSessionRepository,
        public EventRepository $eventRepository,
        public RoleRepository $roleRepository
    ) {
    }

    function getUsersWithRolesToEvent($eventId, $roleName = null)
    {
        return $this->eventRepository->getUsersWithRolesForCurrentAuthedUser($eventId, $roleName);
    }

    function getCurrentUserRolesToEvent($eventId)
    {
        return $this->eventRepository->getCurrentUserRoles(eventId: $eventId);
    }

    function getCurrentUserRolesByEventSessionId($eventSessionId)
    {
        return $this->eventSessionRepository->getCurrentUserRolesByEventSession($eventSessionId);
    }

    function isCurrentAuthedUserAllowed($eventSessionId)
    {
        return $this->eventSessionRepository->isCurrentAuthedUserAllowed($eventSessionId);
    }

    function getAccessGroupIdByEventSessionId($eventSessionId)
    {
        return $this->eventSessionRepository->getAccessGroupId($eventSessionId);
    }

    function attachRoleToEvent($eventAccessRequest, $eventId, $checkIfUserExist = false)
    {
        $user = $this->userRepository->findByEmail($eventAccessRequest['email']);

        if (!$user && empty($user)) {
            if ($checkIfUserExist) {
                throw new WrongCredentialException(__('Wrong credentials error: User by provided email is not exist!'));
            } else {
                $user = $this->userRepository->createByEmail($eventAccessRequest['email']);
            }
        }

        $event = $this->eventRepository->findByIdForCurrentAuthedUser($eventId);

        CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($event['access_group_id']))
            ->forget(CacheKeys::rolesByUserIdKey($user['id']));

        return $this->attachRoleToEventByUser($user, $event, $eventAccessRequest['role_id']);
    }



    function detachRoleToEvent($eventAccessRequest, $eventId)
    {
        $user = $this->userRepository->findByEmail($eventAccessRequest['email']);

        if (!$user) {
            throw new WrongCredentialException(__('Wrong credentials error: User by provided email is not exist!'));
        }

        $event = $this->eventRepository->findByIdForCurrentAuthedUser($eventId);

        if (!$event->access_group_id) {
            return true;
        }

        CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($event['access_group_id']))
            ->forget(CacheKeys::rolesByUserIdKey($user['id']));

        return $user->detachRolesForAccessGroups($eventAccessRequest['role_id'], $event->access_group_id);
    }

    function attachRoleToEventByUser($user, $event, $roleId)
    {

        if (!$event->access_group_id) {
            $accessGroup = $this->accessGroupRepository->create([
                'name' => 'access_for_event_' . $event->id,
                'display_name' => __("Access group for event: {$event->name}")
            ]);

            $event->access_group_id = $accessGroup->id;
            $event->save();
        }

        $user->attachRoleToAccessGroup($roleId, $event->access_group_id);

        CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($event['access_group_id']))
            ->flush();

        return $user;
    }

    function attachRoleAndEventTicketToAccessGroup($user, $event, $roleId, $eventTicketId)
    {

        if (!$event->access_group_id) {
            $accessGroup = $this->accessGroupRepository->create([
                'name' => 'access_for_event_' . $event->id,
                'display_name' => __("Access group for event: {$event->name}")
            ]);

            $event->access_group_id = $accessGroup->id;
            $event->save();
        }

        $user->attachRoleAndEventTicketToAccessGroup($roleId, $eventTicketId, $event->access_group_id);

        return $user;
    }

    function getRolesByCurrentUserAndAccessGroupId($accessGroupId)
    {
        return $this->roleRepository->getRolesByCurrentUserAndAccessGroupId($accessGroupId);
    }
}
