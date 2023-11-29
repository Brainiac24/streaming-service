<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Constants\Roles;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Requests\Poll\CreatePollRequest;
use App\Http\Requests\Poll\PollOptionVoteRequest;
use App\Http\Requests\Poll\UpdatePollRequest;
use App\Http\Requests\Poll\UpdatePollTypeIdRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Poll\PollChannelResource;
use App\Http\Resources\Poll\PollItemResource;
use App\Http\Resources\Poll\PollListResource;
use App\Services\Cache\CacheServiceFacade;
use App\Services\ChatMessage\ChatMessageService;
use App\Services\Poll\PollService;
use App\Services\WebSocket\WebSocketService;
use Auth;
use DB;
use Illuminate\Support\Facades\Response;

class PollController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'PollController:list',
        'findById' => 'PollController:findById',
        'create' => 'PollController:create',
        'update' => 'PollController:update',
        'delete' => 'PollController:delete'
    ];

    public function __construct(
        public PollService $pollService,
        public ChatMessageService $chatMessageService,
        public WebSocketService $webSocketService
    ) {
        //
    }

    public function list($eventSessionId)
    {
        $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($eventSessionId);
        return Response::apiSuccess(
            new PollListResource($this->pollService->list($eventSessionId), $accessGroupId)
        );
    }


    public function findById($id)
    {
        //
    }


    public function create(CreatePollRequest $request)
    {
        DB::beginTransaction();
        try {
            $requestData = $request->validated();
            $poll = $this->pollService->create($requestData);

            CacheServiceFacade::tags(CacheKeys::eventSessionIdTag($poll['event_session_id']))
                ->flush();

            $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

            $socketData = new BaseJsonResource(
                data: [
                    'poll' => (new PollItemResource($poll, $accessGroupId))?->toArray()['data'] ?? []
                ],
                mutation: WebSocketMutations::SOCK_POLL_NEW,
                scope: WebSocketScopes::EVENT
            );

            $this->webSocketService->publishByEventSessionId($socketData, $poll['event_session_id'], true);

            if (isset($requestData['is_publish']) && $requestData['is_publish']) {
                $this->updateStatusToStarted($poll['id']);
            }

            $result =  Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
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


    public function update(UpdatePollRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $requestData = $request->validated();
            $poll = $this->pollService->update($requestData, $id);

            CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
                ->flush();

            $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

            $socketData = new BaseJsonResource(
                data: [
                    'poll' => (new PollItemResource($poll, $accessGroupId))?->toArray()['data'] ?? []
                ],
                mutation: WebSocketMutations::SOCK_EDIT_POLL,
                scope: WebSocketScopes::EVENT
            );

            $this->webSocketService->publish([$poll['private_channel']], $socketData);

            if (isset($requestData['is_publish']) && $requestData['is_publish']) {
                $this->updateStatusToStarted($poll['id']);
            }

            $result = Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
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
        $accessGroupId = $this->pollService->pollRepository->accessGroupIdByPollId($id);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        CacheServiceFacade::forget(CacheKeys::accessGroupByPollIdKey($id));

        $poll = $this->pollService->pollRepository->findById($id)->toArray();

        $this->pollService->delete($id);

        CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
            ->flush();

        $socketData = new BaseJsonResource(
            data: [
                'poll_id' => $id
            ],
            mutation: WebSocketMutations::SOCK_POLL_DELETE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publish([$poll['channel'], $poll['private_channel']], $socketData);

        return Response::apiSuccess();
    }

    public function updateType(UpdatePollTypeIdRequest $request, $id)
    {
        $poll = $this->pollService->updateType($request->validated(), $id);

        CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
            ->flush();

        $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        $socketData = new BaseJsonResource(
            data: [
                'poll' => (new PollItemResource($poll, $accessGroupId))?->toArray()['data'] ?? []
            ],
            mutation: WebSocketMutations::SOCK_UPDATE_POLL_STYLE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publish([$poll['channel'], $poll['private_channel']], $socketData);

        return Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
    }

    public function updateStatusToStarted($id)
    {
        $poll = $this->pollService->updateStatusToStarted($id);

        CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
            ->flush();

        $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        $socketDataPrivate = new BaseJsonResource(
            data: [
                'poll' => (new PollChannelResource($poll, "private_channel"))?->toArray()['data'] ?? []
            ],
            mutation: WebSocketMutations::SOCK_POLL_SHARE,
            scope: WebSocketScopes::EVENT
        );

        $socketDataPublic = new BaseJsonResource(
            data: [
                'poll' => (new PollChannelResource($poll, "channel"))?->toArray()['data'] ?? []
            ],
            mutation: WebSocketMutations::SOCK_POLL_SHARE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByEventSessionId($socketDataPrivate, $poll['event_session_id'],true);
        $this->webSocketService->publishByEventSessionId($socketDataPublic, $poll['event_session_id']);

        return Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
    }

    public function updateStatusToFinished($id)
    {
        $poll = $this->pollService->updateStatusToFinished($id);

        CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
            ->flush();

        $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        $socketData = new BaseJsonResource(
            data: [
                'poll' => (new PollItemResource($poll, $accessGroupId))?->toArray()['data'] ?? []
            ],
            mutation: WebSocketMutations::SOCK_POLL_CLOSE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publish([$poll['channel'], $poll['private_channel']], $socketData);

        return Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
    }

    public function showResults($id)
    {
        $poll = $this->pollService->updateIsPublicResults($id, true);

        CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
            ->flush();

        $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        $socketData = new BaseJsonResource(
            data: [
                'poll' => (new PollItemResource($poll, $accessGroupId))?->toArray()['data'] ?? []
            ],
            mutation: WebSocketMutations::SOCK_POLL_SHOW,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByEventSessionId($socketData, $poll['event_session_id']);

        return Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
    }

    public function hideResults($id)
    {
        $poll = $this->pollService->updateIsPublicResults($id, false);

        CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
            ->flush();

        $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        $socketData = new BaseJsonResource(
            data: [
                'poll' => (new PollItemResource($poll, $accessGroupId))?->toArray()['data'] ?? []
            ],
            mutation: WebSocketMutations::SOCK_POLL_HIDE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publish([$poll['channel'], $poll['private_channel']], $socketData);

        return Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
    }

    public function vote(PollOptionVoteRequest $request, $id)
    {
        $poll = $this->pollService->vote($request->validated(), $id);

        CacheServiceFacade::tags([CacheKeys::pollIdTag($id)])
            ->flush();

        $accessGroupId = $this->pollService->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        $socketData = new BaseJsonResource(
            data: [
                'poll' => (new PollItemResource($poll, $accessGroupId))?->toArray()['data'] ?? []
            ],
            mutation: WebSocketMutations::SOCK_UPDATE_POLL,
            scope: WebSocketScopes::EVENT
        );

        $socketChannels = [$poll['private_channel']];
        if ($poll['is_public_results']) {
            $socketChannels[] = $poll['channel'];
        }

        $this->webSocketService->publish($socketChannels, $socketData);

        return Response::apiSuccess(new PollItemResource($poll, $accessGroupId));
    }
}
