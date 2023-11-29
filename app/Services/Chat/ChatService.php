<?php

namespace App\Services\Chat;

use App\Constants\ChatMessageTypes;
use App\Http\Resources\ChatMessage\ChatMessageListResource;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Repositories\ChatMessageLike\ChatMessageLikeRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Services\ChatMessage\ChatMessageService;
use Str;

class ChatService
{
    public function __construct(
        public ChatRepository $chatRepository,
        public ChatMessageService $chatMessageService,
        public ChatMessageRepository $chatMessageRepository,
        public ChatMessageLikeRepository $chatMessageLikeRepository,
        public EventSessionRepository $eventSessionRepository
    ) {
    }


    public function createWithMessagesAndLikes($eventSession, $fare)
    {
        $data = [];
        $data['event_session_id'] = $eventSession->id;
        $data['message_channel'] = Str::lower(Str::random(28));
        $data['question_channel'] = Str::lower(Str::random(28));
        $data['is_messages_enabled'] = true;
        $data['is_questions_enabled'] = false;
        $data['is_question_messages_enabled'] = true;
        $data['is_question_moderation_enabled'] = false;

        $chat = $this->chatRepository->create($data);

        $this->chatMessageRepository->createTableByChatId($chat->id);
        $this->chatMessageLikeRepository->createTableByChatId($chat->id);

        return $chat;
    }

    public function getChatForStreamRoom($eventId, $eventSessionId, $isQuestionsEnabled)
    {
        $chat = $this->chatRepository->findByEventSessionId($eventSessionId);

        $chatMessages = (new ChatMessageListResource($this->chatMessageService->listByChatId($eventId, $eventSessionId, $chat['id'], ChatMessageTypes::MESSAGE)))?->toArray();
        $chatQuestions = [];
        if ($isQuestionsEnabled) {
            $chatQuestions = (new ChatMessageListResource($this->chatMessageService->listByChatId($eventId, $eventSessionId, $chat['id'], ChatMessageTypes::QUESTION)))?->toArray();
        }
        $chatPinnedMessages = (new ChatMessageListResource($this->chatMessageService->listByChatId($eventId, $eventSessionId, $chat['id'], ChatMessageTypes::MESSAGE, true)))?->toArray();
        $chat['chat_messages'] = $chatMessages['data'] ?? [];
        $chat['chat_questions'] = $chatQuestions['data'] ?? [];
        $chat['chat_pinned_messages'] = $chatPinnedMessages['data'] ?? [];

        return $chat;
    }
}
