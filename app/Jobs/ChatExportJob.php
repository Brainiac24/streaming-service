<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Chat\ChatExportResource;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\ChatMessage\ChatMessageService;
use App\Services\Helper\XlsxExportHelperService;
use App\Services\WebSocket\WebSocketService;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Route;

class ChatExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(public $chatId, public $chatMessageTypeId)
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
        $webSocketService = app()->make(WebSocketService::class);
        $chatMessageRepository = app()->make(ChatMessageRepository::class);
        $chatRepository = app()->make(ChatRepository::class);
        $xlsxExportHelperService = app()->make(XlsxExportHelperService::class);
        $chatMessages = $chatMessageRepository->listByChatIdWithoutPagination($this->chatId, $this->chatMessageTypeId);
        $user = $chatRepository->findUserByChatId($this->chatId);
        $data = (new ChatExportResource($chatMessages))->toArray();
        $fileName = '';
        $link = '';
        if ($this->chatMessageTypeId == ChatMessageTypes::MESSAGE) {
            $fileName = '/chat_messages_' . $this->chatId . '_' . time() . '_list.xlsx';
            $link = url('api/v1/chats/'.$this->chatId.'/messages/export');
        } else if ($this->chatMessageTypeId == ChatMessageTypes::QUESTION) {
            $fileName = '/chat_questions_' . $this->chatId . '_' . time() . '_list.xlsx';
            $link = url('api/v1/chats/'.$this->chatId.'/questions/export');
        }
        $exportFile =  $xlsxExportHelperService->exportFile($data, $fileName);
        CacheServiceFacade::set(
            CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdKey($this->chatId, $this->chatMessageTypeId),
            $exportFile,
            config('cache.ttl_ten_minute')
        );

        $socketData = new BaseJsonResource(
            data: [
                'link' => $link
            ],
            mutation: WebSocketMutations::SOCK_FILE_EXPORT,
            scope: WebSocketScopes::CMS
        );

        $webSocketService->publish([$user['channel']], $socketData);
    }
}
