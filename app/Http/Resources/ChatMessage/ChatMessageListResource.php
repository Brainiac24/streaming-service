<?php

namespace App\Http\Resources\ChatMessage;

use App\Http\Resources\BaseJsonResource;
use Auth;

class ChatMessageListResource extends BaseJsonResource
{
    public function __construct($chatMessages)
    {
        parent::__construct(data: $chatMessages);
        $this->data = [];

        foreach ($chatMessages as $chatMessage) {

            $this->data[] = [
                "id" => $chatMessage['id'],
                "text" => $chatMessage['text'],
                "reply_to_chat_message_id" => $chatMessage['reply_to_chat_message_id'],
                "reply_to_chat_message" => $chatMessage['reply_to_chat_message'] ?? null,
                "likes_count" => $chatMessage['likes_count'],
                "is_current_user_liked" => $chatMessage['liked_users'][Auth::id()] ?? false,
                "is_pinned" => $chatMessage['is_pinned'],
                "is_answered" => $chatMessage['is_answered'],
                "is_moderation_passed" => $chatMessage['is_moderation_passed'],
                "chat_message_type_id" => $chatMessage['chat_message_type_id'],
                "created_at" => $chatMessage['created_at'],
                "updated_at" => $chatMessage['updated_at'],
                "user" => $chatMessage['user'],
            ];
        }
    }
}
