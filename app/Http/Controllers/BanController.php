<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Constants\Roles;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Requests\Ban\CreateBanRequest;
use App\Http\Requests\Ban\UpdateBanRequest;
use App\Http\Resources\Bans\BanItemResource;
use App\Http\Resources\Bans\BanListResource;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\Ban\BanRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Chat\ChatService;
use App\Services\WebSocket\WebSocketService;
use Auth;
use Response;

class BanController extends Controller
{

    const LIST_ACTION = 'BanController:list';
    const FIND_BY_ID_ACTION = 'BanController:findById';
    const CREATE_ACTION = 'BanController:create';
    const UPDATE_ACTION = 'BanController:update';
    const DELETE_ACTION = 'BanController:delete';

    const ACTION_PERMISSIONS = [
        self::LIST_ACTION => true,
        self::FIND_BY_ID_ACTION => true,
        self::CREATE_ACTION => true,
        self::UPDATE_ACTION => true,
        self::DELETE_ACTION => true
    ];

    public function __construct(
        private BanRepository $banRepository,
        private ChatService $chatService,
        public WebSocketService $webSocketService,
        public UserRepository $userRepository,
        public EventRepository $eventRepository
    ) {
        //
    }

    public function list($eventId)
    {
        $event = $this->eventRepository->findById($eventId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($event['access_group_id'], [Roles::ADMIN, Roles::MODERATOR]);

        return Response::apiSuccess(
            new BanListResource(data: $this->banRepository->list($eventId))
        );
    }


    public function findById($eventId, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->banRepository->findByIdForCurrentAuthedUser($eventId, $id))
        );
    }


    public function create(CreateBanRequest $request)
    {
        $requestData = $request->validated();

        $event = $this->eventRepository->findById($requestData['event_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($event['access_group_id'], [Roles::ADMIN, Roles::MODERATOR]);

        $data = $this->banRepository->create($requestData);

        $socketData = new BaseJsonResource(
            data: (new BanItemResource(data: $data))->toArray()['data'],
            mutation: WebSocketMutations::SOCK_BAN_USER,
            scope: WebSocketScopes::EVENT
        );
        $this->webSocketService->publish($this->userRepository->findById($requestData['user_id'])->channel, $socketData);

        return Response::apiSuccess(new BanItemResource(data: $data));
    }


    public function update(UpdateBanRequest $request, $id)
    {
        return Response::apiSuccess(
            new BanItemResource(data: $this->banRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $accessGroupId = $this->banRepository->accessGroupIdByBanId($id);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        CacheServiceFacade::forget(CacheKeys::accessGroupByBanIdKey($id));

        $ban = $this->banRepository->findById($id);

        $this->banRepository->deleteByModel($ban);

        $socketData = new BaseJsonResource(
            data: [
                'event_id' => $ban['event_id'],
                'user_id' => $ban['user_id']
            ],
            mutation: WebSocketMutations::SOCK_UNBAN_USER,
            scope: WebSocketScopes::EVENT
        );
        $this->webSocketService->publish($this->userRepository->findById($ban['user_id'])->channel, $socketData);

        return Response::apiSuccess();
    }
}