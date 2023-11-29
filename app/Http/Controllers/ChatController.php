<?php

namespace App\Http\Controllers;

use App\Constants\ChatMessageTypes;
use App\Repositories\Chat\ChatRepository;
use App\Services\ChatMessage\ChatMessageService;
use Storage;

class ChatController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ChatController:list',
        'findById' => 'ChatController:findById',
        'create' => 'ChatController:create',
        'update' => 'ChatController:update',
        'delete' => 'ChatController:delete'
    ];

    public function __construct(private ChatRepository $chatRepository,public ChatMessageService $chatMessageService)
    {
        //
    }
}
