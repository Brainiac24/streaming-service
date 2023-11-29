<?php

namespace App\Services\ChatMessage;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\StatusCodes;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Resources\BaseJsonResource;
use App\Jobs\ChatAddMessageCacheJob;
use App\Jobs\ChatExportJob;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Repositories\User\UserRepository;
use App\Services\Helper\XlsxExportHelperService;
use App\Services\WebSocket\WebSocketService;
use Cache;
use Illuminate\Support\Facades\Auth;
use Response;
use Storage;

class ChatMessageService
{
    public function __construct(
        public ChatMessageRepository $chatMessageRepository,
        public ChatRepository $chatRepository,
        public XlsxExportHelperService $xlsxExportHelperService,
        public WebSocketService $webSocketService,
        public UserRepository $userRepository
    ) {
    }

    public function listByChatId($eventId, $eventSessionId, $chatId, $chatMessageTypeId, $isPinnedOnly = false)
    {

        $chatMessages = $this->chatMessageRepository->listByChatId($eventId, $eventSessionId, $chatId, $chatMessageTypeId, $isPinnedOnly);

        return $this->parseChatMessages($chatMessages);
    }

    public function createByChatId($data, $chatId, $chatMessageTypeId)
    {
        try {

            $data['user_id'] = Auth::id();
            $data['chat_id'] = $chatId;
            $data['chat_message_type_id'] = $chatMessageTypeId;

            $chatMessage = $this->chatMessageRepository->createByChatId($data, $chatId);
        } catch (\Illuminate\Database\QueryException $e) {
            if (strpos($e->getMessage(), 'Base table or view not found')) {
                $this->chatMessageRepository->createTableByChatId($chatId);
                $chatMessage = $this->chatMessageRepository->create($data);
            } else {
                throw $e;
            }
        }

        $chatMessage = $this->chatMessageRepository->findByChatMessageId($chatId, $chatMessage['id'])->toArray();

        $chatMessage = $this->parseChatMessages($chatMessage)[0];

        ChatAddMessageCacheJob::dispatch($chatId, $chatMessageTypeId, $chatMessage);

        return $chatMessage;
    }

    public function parseChatMessages($chatMessages)
    {
        $chatMessageResult = [];

        if (!is_array($chatMessages)) {
            $chatMessages = $chatMessages->toArray();
        }

        foreach ($chatMessages as $chatMessage) {

            if (!isset($chatMessageResult[$chatMessage['id']])) {
                $chatMessageResult[$chatMessage['id']] = $chatMessage;
            }

            if (!isset($chatMessageResult[$chatMessage['id']]['user'])) {
                $user = &$chatMessageResult[$chatMessage['id']]['user'];
                $user = [
                    'id' => $chatMessage['user_id'],
                    'name' => $chatMessage['user_name'],
                    'lastname' => $chatMessage['user_lastname'],
                    'fullname' => trim($chatMessage['user_name'] . ' ' . $chatMessage['user_lastname']),
                    'email' => $chatMessage['user_email'],
                    'avatar_path' => $chatMessage['user_avatar_path'],
                ];
            }

            $roles = &$user['roles'];
            if ($chatMessage['role_id']) {
                $roles[$chatMessage['role_id']] = [
                    'id' => $chatMessage['role_id'],
                    'name' => $chatMessage['role_name'],
                    'label' => __($chatMessage['role_label']),
                ];
            }

            $likedUsers = &$chatMessageResult[$chatMessage['id']]['liked_users'];
            if ($chatMessage['chat_message_like_user_id']) {
                $likedUsers[$chatMessage['chat_message_like_user_id']] = $chatMessage['chat_message_like_user_id'];
            }


            if (!empty($chatMessage['reply_to_chat_message_id'])) {
                if (!isset($chatMessageResult[$chatMessage['id']]['reply_to_chat_message'])) {
                    $chatMessageResult[$chatMessage['id']]['reply_to_chat_message'] = [
                        'user_id' => $chatMessage['reply_user_id'],
                        'chat_id' => $chatMessage['reply_chat_id'],
                        'reply_to_chat_message_id' => $chatMessage['reply_reply_to_chat_message_id'],
                        'text' => $chatMessage['reply_text'],
                        'likes_count' => $chatMessage['reply_likes_count'],
                        'is_pinned' => $chatMessage['reply_is_pinned'],
                        'is_answered' => $chatMessage['reply_is_answered'],
                        'is_moderation_passed' => $chatMessage['reply_is_moderation_passed'],
                        'chat_message_type_id' => $chatMessage['reply_chat_message_type_id'],
                        'created_at' => $chatMessage['reply_created_at'],
                        'updated_at' => $chatMessage['reply_updated_at'],
                    ];
                }

                if (!isset($chatMessageResult[$chatMessage['id']]['reply_to_chat_message']['user'])) {
                    $repliedUser = &$chatMessageResult[$chatMessage['id']]['reply_to_chat_message']['user'];
                    $repliedUser = [
                        'id' => $chatMessage['reply_user_id'],
                        'name' => $chatMessage['reply_user_name'],
                        'lastname' => $chatMessage['reply_user_lastname'],
                        'fullname' => trim($chatMessage['reply_user_name'] . ' ' . $chatMessage['reply_user_lastname']),
                        'email' => $chatMessage['reply_user_email'],
                        'avatar_path' => $chatMessage['reply_user_avatar_path'],
                    ];
                }

                $repliedRoles = &$repliedUser['roles'];
                $repliedRoles[$chatMessage['reply_role_id']] = [
                    'id' => $chatMessage['reply_role_id'],
                    'name' => $chatMessage['reply_role_name'],
                    'label' => __($chatMessage['reply_role_label']),
                ];
            }
        }

        $chatMessageResult = array_values($chatMessageResult);

        foreach ($chatMessageResult as &$item) {
            $item['user']['roles'] = !empty($item['user']['roles']) ? array_values($item['user']['roles']) : [];
            if (isset($item['reply_to_chat_message']['user']['roles'])) {
                $item['reply_to_chat_message']['user']['roles'] = array_values($item['reply_to_chat_message']['user']['roles']);
            }
        }

        return $chatMessageResult;
    }


    public function pin($eventId, $eventSessionId, $chatId, $id)
    {
        $chatMessage = $this->chatMessageRepository->findByIdAndChatId($id, $chatId);

        if ($chatMessage['is_pinned']) {
            throw new ValidationException('Validation error: Chat message is already pinned!');
        }

        $this->chatMessageRepository->updateAllIsPinnedToFalseByChatId($chatId);

        $data = [
            'is_pinned' => true
        ];

        $chatMessage = $this->chatMessageRepository->updateByChatId($data, $id, $chatId);

        $chatMessage = $this->chatMessageRepository->findByChatMessageId($chatId, $chatMessage['id'])->toArray();

        return $this->parseChatMessages($chatMessage)[0];
    }

    public function unpin($chatId, $id)
    {
        $chatMessage = $this->chatMessageRepository->findByIdAndChatId($id, $chatId);

        /*if (!$chatMessage['is_pinned']) {
            throw new ValidationException('Validation error: Chat message is already unpinned!');
        }*/

        $data = [
            'is_pinned' => false
        ];
        return $this->chatMessageRepository->updateByChatId($data, $id, $chatId);
    }

    public function answered($chatId, $id)
    {
        $chatMessage = $this->chatMessageRepository->findByIdAndChatId($id, $chatId);

        if ($chatMessage['is_answered']) {
            throw new ValidationException('Validation error: Chat message is already answered!');
        }

        $data = [
            'is_answered' => true,
            'is_moderation_passed' => true
        ];
        return $this->chatMessageRepository->updateByChatId($data, $id, $chatId);
    }

    public function moderated($chatId, $id)
    {
        $chatMessage = $this->chatMessageRepository->findByIdAndChatId($id, $chatId);

        if ($chatMessage['is_moderation_passed']) {
            throw new ValidationException('Validation error: Chat message is already moderated!');
        }
        $data = [
            'is_moderation_passed' => true
        ];
        return $this->chatMessageRepository->updateByChatId($data, $id, $chatId);
    }

    public function changeTypeToQuestion($chatId, $id)
    {
        $chatMessage = $this->chatMessageRepository->findByIdAndChatId($id, $chatId);

        if ($chatMessage['is_pinned']) {
            throw new ValidationException('Validation error: Pinned chat message type can not be changed to question!');
        }

        if ($chatMessage['chat_message_type_id'] == ChatMessageTypes::QUESTION) {
            throw new ValidationException('Validation error: Chat message type is already changed to question!');
        }
        $data = [
            'chat_message_type_id' => ChatMessageTypes::QUESTION
        ];
        return $this->chatMessageRepository->updateByChatId($data, $id, $chatId);
    }

    public function getChatMessagesForExport($chatId, $chatMessageType)
    {

        if (!$this->chatRepository->findByIdForCurrentAuthedUser($chatId)){
            throw new NotFoundException();
        }

        $chatMessagesFile = Cache::get(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdKey($chatId, $chatMessageType));

        if (!$chatMessagesFile) {
            ChatExportJob::dispatch($chatId, $chatMessageType);
            return Response::apiSuccess(
                new BaseJsonResource(
                    code: StatusCodes::IN_PROCESS,
                    message: __('In process'),
                )
            );
        }
        $link = '';
        if ($chatMessageType == ChatMessageTypes::MESSAGE) {
            $link = url('api/v1/chats/' . $chatId . '/messages/export');
        } else if ($chatMessageType == ChatMessageTypes::QUESTION) {
            $link = url('api/v1/chats/' . $chatId . '/questions/export');
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

    public function download($chatId, $chatMessageType)
    {

        if (!$this->chatRepository->findByIdForCurrentAuthedUser($chatId)) {
            throw new NotFoundException();
        }

        $chatMessagesFile = Cache::get(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdKey($chatId, $chatMessageType));
        if (!$chatMessagesFile) {
            return Response::apiError(
                new BaseJsonResource(
                    code: StatusCodes::NOT_FOUND_ERROR,
                    message: __('Not found!')
                ),
                404
            );
        }
        return Storage::disk("local")->download('local/xlsx/' . $chatMessagesFile, headers: [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Length' => null,
        ]);
    }
}
