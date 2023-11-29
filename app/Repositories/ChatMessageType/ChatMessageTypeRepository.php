<?php

namespace App\Repositories\ChatMessageType;

use App\Models\ChatMessageType;
use App\Repositories\BaseRepository;

class ChatMessageTypeRepository extends BaseRepository
{
    public function __construct(public ChatMessageType $chatMessageType)
    {
        parent::__construct($chatMessageType);
    }
}