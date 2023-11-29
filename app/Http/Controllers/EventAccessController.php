<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventAccess\CreateEventAccessRequest;
use App\Http\Requests\EventAccess\DetachEventAccessRequest;
use App\Http\Resources\Role\RoleListResource;
use App\Http\Resources\User\UserBasicInfoArrayResource;
use App\Http\Resources\User\UserBasicInfoResource;
use App\Services\EventAccess\EventAccessService;
use Illuminate\Support\Facades\Response;

class EventAccessController extends Controller
{

    static $actionPermissionMap = [
        'getUsersWithRolesToEvent' => 'EventAccessController:getUsersWithRolesToEvent',
        'attachRoleToEvent' => 'EventAccessController:attachRoleToEvent',
        'detachRoleToEvent' => 'EventAccessController:detachRoleToEvent',
    ];

    public function __construct(private EventAccessService $eventAccessService)
    {
    }

    public function getUsersWithRolesToEvent($eventId, $roleName = null)
    {
        return Response::apiSuccess(
            new UserBasicInfoArrayResource($this->eventAccessService->getUsersWithRolesToEvent($eventId, $roleName))
        );
    }

    public function getCurrentUserRolesToEvent($eventId)
    {
        return Response::apiSuccess(
            new RoleListResource($this->eventAccessService->getCurrentUserRolesToEvent($eventId))
        );
    }

    public function attachRoleToEventIfUserExist(CreateEventAccessRequest $request, $id)
    {
        $user = $this->eventAccessService->attachRoleToEvent($request->validated(), $id, true);

        return Response::apiSuccess(new UserBasicInfoResource($user));
    }

    public function attachRoleToEvent(CreateEventAccessRequest $request, $eventId)
    {
        $user = $this->eventAccessService->attachRoleToEvent($request->validated(), $eventId);

        return Response::apiSuccess(new UserBasicInfoResource($user));
    }

    public function detachRoleToEvent(DetachEventAccessRequest $request, $eventId)
    {
        $this->eventAccessService->detachRoleToEvent($request->validated(), $eventId);

        return Response::apiSuccess();
    }
}
