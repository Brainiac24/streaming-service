<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Repositories\Chat\ChatRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\ChatMessage\ChatMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

    class ChatAddMessageCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(public $chatId, public $chatMessageTypeId, public $chatMessage)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $chatMessages = CacheServiceFacade::get(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($this->chatId, $this->chatMessageTypeId, false));

        if ($chatMessages) {
            if (count($chatMessages) >= 50) {
                array_shift($chatMessages);
            }
            $chatMessages[] = $this->chatMessage;
        } else {
            $chatMessageService = app()->make(ChatMessageService::class);
            $chatRepository = app()->make(ChatRepository::class);
            $eventSession = $chatRepository->findEventSessionByChatId($this->chatId);

            $chatMessages = $chatMessageService->listByChatId($eventSession['event_id'], $eventSession['id'], $this->chatId, $this->chatMessageTypeId, false);
        }

        CacheServiceFacade::set(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($this->chatId, $this->chatMessageTypeId, false), $chatMessages);
    }
}
