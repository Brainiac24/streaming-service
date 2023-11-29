<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\EventSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventSessionUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public EventSession $eventSession, public EventSession $oldEventSession, public $chat, public $oldChat)
    {
    }
}
