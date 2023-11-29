<?php

namespace App\Http\Resources\Chat;

use App\Http\Resources\BaseJsonResource;

class ChatItemRoomResource extends BaseJsonResource
{
    public function __construct($data)
    {
        if (empty($data)) {
            $this->data = [];
            return;
        }

        $this->data = [
            'id' => $data['id'],
            'event_session_id' => $data['event_session_id'],
            'message_channel' => $data['message_channel'],
            'question_channel' => $data['question_channel'],
            'is_messages_enabled' => $data['is_messages_enabled'],
            'is_question_messages_enabled' => $data['is_question_messages_enabled'],
            'is_question_moderation_enabled' => $data['is_question_moderation_enabled'],
            'is_active' => $data['is_active'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'chat_pinned_messages' => $data['chat_pinned_messages'],
            'chat_messages' => $data['chat_messages'],
            'chat_questions' => $data['chat_questions'],

        ];
    }
}
