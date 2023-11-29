<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\Chat\ChatRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\ChatMessage\ChatMessageService;
use App\Services\ChatMessageLike\ChatMessageLikeService;
use App\Services\WebSocket\WebSocketService;
use Illuminate\Support\Facades\Response;

class ChatMessageLikeController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ChatMessageLikeController:list',
        'findById' => 'ChatMessageLikeController:findById',
        'create' => 'ChatMessageLikeController:create',
        'update' => 'ChatMessageLikeController:update',
        'delete' => 'ChatMessageLikeController:delete'
    ];

    public function __construct(
        private ChatMessageLikeService $chatMessageLikeService,
        private ChatMessageService $chatMessageService,
        private WebSocketService $webSocketServicee,
        private ChatRepository $chatRepository
    ) {
        //
    }

    public function list($chatId, $chatMessageId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->chatMessageLikeService->listByChatId($chatId, $chatMessageId))
        );
    }

    public function create($chatId, $chatMessageId)
    {

        $data = $this->chatMessageLikeService->createByChatId($chatId, $chatMessageId);

        $eventSession=$this->chatRepository->findEventSessionByChatId($chatId);
        
        CacheServiceFacade::tags(
            CacheKeys::eventSessionIdTag($eventSession['id'])
        )->flush();

        $chatMessage = $this->chatMessageService->chatMessageRepository->findByIdAndChatId($chatMessageId, $chatId);

        $socketData = new BaseJsonResource(
            data: [
                'chat_id' => $chatId,
                'message_id' => $chatMessageId,
                'likes_count' => $chatMessage['likes_count']
            ],
            mutation: WebSocketMutations::SOCK_SET_MESSAGE_LIKE,
            scope: WebSocketScopes::EVENT
        );

        $isMessageTypeQuestion = $chatMessage['chat_message_type_id'] == ChatMessageTypes::QUESTION;

        $this->webSocketServicee->publishByChatId($socketData, $chatId, $isMessageTypeQuestion);



        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }

    public function delete($chatId, $chatMessageId)
    {
        $this->chatMessageLikeService->deleteByChatId($chatId, $chatMessageId);

        $eventSession=$this->chatRepository->findEventSessionByChatId($chatId);
        
        CacheServiceFacade::tags(
            CacheKeys::eventSessionIdTag($eventSession['id'])
        )->flush();

        $chatMessage = $this->chatMessageService->chatMessageRepository->findByIdAndChatId($chatMessageId, $chatId);

        $socketData = new BaseJsonResource(
            data: [
                'chat_id' => $chatId,
                'message_id' => $chatMessageId,
                'likes_count' => $chatMessage['likes_count']
            ],
            mutation: WebSocketMutations::SOCK_SET_MESSAGE_LIKE,
            scope: WebSocketScopes::EVENT
        );

        $isMessageTypeQuestion = $chatMessage['chat_message_type_id'] == ChatMessageTypes::QUESTION;

        $this->webSocketServicee->publishByChatId($socketData, $chatId, $isMessageTypeQuestion);

        return Response::apiSuccess();
    }
}
