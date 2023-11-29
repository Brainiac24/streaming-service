<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends BaseModel
{
    use SoftDeletes;
    public $fillable = [
        'user_id',
        'chat_id',
        'reply_to_chat_message_id',
        'text',
        'likes_count',
        'is_pinned',
        'is_answered',
        'is_moderation_passed',
        'chat_message_type_id',
    ];
}
