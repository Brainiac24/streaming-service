<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\Roles;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Exceptions\NotFoundException;
use App\Http\Requests\ChatMessage\CreateChatMessageRequest;
use App\Http\Requests\ChatMessage\UpdateChatMessageRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\ChatMessage\ChatMessageItemResource;
use App\Http\Resources\ChatMessage\ChatMessageListResource;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Services\Ban\BanService;
use App\Services\Cache\CacheServiceFacade;
use App\Services\ChatMessage\ChatMessageService;
use App\Services\WebSocket\WebSocketService;
use Auth;
use Illuminate\Support\Facades\Response;

class ChatMessageController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ChatMessageController:list',
        'findById' => 'ChatMessageController:findById',
        'create' => 'ChatMessageController:create',
        'update' => 'ChatMessageController:update',
        'delete' => 'ChatMessageController:delete'
    ];

    public function __construct(
        private ChatMessageRepository $chatMessageRepository,
        public ChatMessageService $chatMessageService,
        public WebSocketService $webSocketService,
        public ChatRepository $chatRepository,
        public BanService $banService,
    ) {
        //
    }

    public function listMessages($chatId)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        $eventSession = $this->chatRepository->findEventSessionByChatId($chatId);

        return Response::apiSuccess(
            new ChatMessageListResource(
                $this->chatMessageService->listByChatId($eventSession['event_id'], $eventSession['id'], $chatId,  ChatMessageTypes::MESSAGE)
            )
        );
    }

    public function createMessage(CreateChatMessageRequest $request, $chatId)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        $event = $this->chatRepository->findEventByChatId($chatId);
        $this->banService->isUserBannedForEventAndFail($event['id']);

        $data = $this->chatMessageService->createByChatId($request->validated(), $chatId,  ChatMessageTypes::MESSAGE);

        $chatMessage = new ChatMessageItemResource(
            [
                'chat_id' => $chatId,
                'message' => $data
            ],
            $this->chatRepository->findById($chatId)
        );

        $socketData = new BaseJsonResource(
            data: $chatMessage->toArray()['data'],
            mutation: WebSocketMutations::SOCK_NEW_MESSAGE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId);

        return Response::apiSuccess($chatMessage);
    }

    public function listQuestions($chatId)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        $eventSession = $this->chatRepository->findEventSessionByChatId($chatId);
        return Response::apiSuccess(
            new ChatMessageListResource(
                $this->chatMessageService->listByChatId($eventSession['event_id'], $eventSession['id'], $chatId,  ChatMessageTypes::QUESTION),
            )
        );
    }

    public function createQuestion(CreateChatMessageRequest $request, $chatId)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        $event = $this->chatRepository->findEventByChatId($chatId);
        $this->banService->isUserBannedForEventAndFail($event['id']);

        $data = $this->chatMessageService->createByChatId($request->validated(), $chatId,  ChatMessageTypes::QUESTION);

        $chatMessage = new ChatMessageItemResource(
            [
                'chat_id' => $chatId,
                'message' => $data
            ],
            $this->chatRepository->findById($chatId)
        );

        $socketData = new BaseJsonResource(
            data: $chatMessage->toArray()['data'],
            mutation: WebSocketMutations::SOCK_NEW_MESSAGE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId, true);

        return Response::apiSuccess($chatMessage);
    }

    public function update(UpdateChatMessageRequest $request, $chatId, $id)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        $chatMessage = $this->chatMessageRepository->updateByChatId($request->validated(), $id, $chatId);

        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $chatMessage['chat_message_type_id'], true));
        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $chatMessage['chat_message_type_id'], false));

        return Response::apiSuccess(
            new BaseJsonResource(data: $chatMessage)
        );
    }

    public function pin($chatId, $id)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $eventSession = $this->chatRepository->findEventSessionByChatId($chatId);

        $data = $this->chatMessageService->pin($eventSession['event_id'], $eventSession['id'], $chatId, $id);

        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $data['chat_message_type_id'], true));

        $chatMessage = new ChatMessageItemResource(
            [
                'chat_id' => $chatId,
                'message' => $data
            ],
            $this->chatRepository->findById($chatId)
        );

        $socketData = new BaseJsonResource(
            data: $chatMessage->toArray()['data'],
            mutation: WebSocketMutations::SOCK_PIN_MESSAGE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId);

        return Response::apiSuccess($chatMessage);
    }

    public function unpin($chatId, $id)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $data = $this->chatMessageService->unpin($chatId, $id);

        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $data['chat_message_type_id'], true));

        $socketData = new BaseJsonResource(
            data: [
                'chat_id' => $chatId,
                'message_id' => $id
            ],
            mutation: WebSocketMutations::SOCK_UNPIN_MESSAGE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId);

        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }

    public function answered($chatId, $id)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $data = $this->chatMessageService->answered($chatId, $id);

        //CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $data['chat_message_type_id'], $data['is_pinned']));

        $socketData = new BaseJsonResource(
            data: [
                'chat_id' => $chatId,
                'message_id' => $id
            ],
            mutation: WebSocketMutations::SOCK_SET_MESSAGE_ANSWERED,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId, true);

        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }
    public function moderated($chatId, $id)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $data = $this->chatMessageService->moderated($chatId, $id);

        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $data['chat_message_type_id'], $data['is_pinned']));

        $socketData = new BaseJsonResource(
            data: [
                'chat_id' => $chatId,
                'message_id' => $id
            ],
            mutation: WebSocketMutations::SOCK_SET_MESSAGE_MODERATION,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId, true);

        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }
    public function changeTypeToQuestion($chatId, $id)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $data = $this->chatMessageService->changeTypeToQuestion($chatId, $id);

        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, ChatMessageTypes::MESSAGE, $data['is_pinned']));
        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, ChatMessageTypes::QUESTION, $data['is_pinned']));

        $socketData = new BaseJsonResource(
            data: [
                'chat_id' => $chatId,
                'message_id' => $id
            ],
            mutation: WebSocketMutations::SOCK_MOVE_MESSAGE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId);

        $chatMessage = $this->chatMessageRepository->findByChatMessageId($chatId, $id)->toArray();

        $chatMessage = $this->chatMessageService->parseChatMessages($chatMessage)[0];

        $chatMessage = (new ChatMessageItemResource(
            [
                'chat_id' => $chatId,
                'message' => $chatMessage
            ],
            $this->chatRepository->findById($chatId)
        ));

        $socketData = new BaseJsonResource(
            data: $chatMessage->toArray()['data'],
            mutation: WebSocketMutations::SOCK_NEW_MESSAGE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByChatId($socketData, $chatId, true);

        return Response::apiSuccess($chatMessage);
    }

    public function delete($chatId, $id)
    {
        $accessGroupId = $this->chatRepository->accessGroupIdByChatId($chatId);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        $chatMessage = $this->chatMessageService->chatMessageRepository->findByIdAndChatId($id, $chatId);

        $this->chatMessageRepository->deleteByChatId($id, $chatId);

        CacheServiceFacade::forget(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $chatMessage['chat_message_type_id'], $chatMessage['is_pinned']));

        $socketData = new BaseJsonResource(
            data: [
                'chat_id' => $chatId,
                'message_id' => $id
            ],
            mutation: WebSocketMutations::SOCK_REMOVE_MESSAGE,
            scope: WebSocketScopes::EVENT
        );

        $isMessageTypeQuestion = $chatMessage['chat_message_type_id'] == ChatMessageTypes::QUESTION;

        $this->webSocketService->publishByChatId($socketData, $chatId, $isMessageTypeQuestion);

        return Response::apiSuccess();
    }

    public function exportMessages($chatId)
    {
        return $this->chatMessageService->getChatMessagesForExport($chatId, ChatMessageTypes::MESSAGE);
    }

    public function exportQuestions($chatId)
    {
        return $this->chatMessageService->getChatMessagesForExport($chatId, ChatMessageTypes::QUESTION);
    }

    public function downloadMessages($chatId){
        return $this->chatMessageService->download($chatId,ChatMessageTypes::MESSAGE);
    }

    public function downloadQuestions($chatId){
        return $this->chatMessageService->download($chatId,ChatMessageTypes::QUESTION);
    }
}
