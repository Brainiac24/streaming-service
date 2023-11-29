<?php

namespace App\Models;

class ChatMessageLike extends BaseModel
{
    public $fillable = [
        'user_id',
        'chat_message_id',
    ];
}