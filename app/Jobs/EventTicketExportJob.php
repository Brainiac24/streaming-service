<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\EventTicketTypes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Chat\ChatExportResource;
use App\Http\Resources\Contact\ContactListResource;
use App\Http\Resources\EventSessionVisit\ExportEventSessionVisitUsersListResource;
use App\Http\Resources\EventTicket\FileEventTicketListResource;
use App\Models\EventDataCollectionTemplate;
use App\Models\EventSessionVisit;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Repositories\Contact\ContactRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventDataCollection\EventDataCollectionRepository;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Repositories\EventSessionVisit\EventSessionVisitRepository;
use App\Repositories\EventTicket\EventTicketRepository;
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

class EventTicketExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(public $eventId, public $status,public $eventTicketData)
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
        $eventTicketRepository = app()->make(EventTicketRepository::class);
        $eventRepository = app()->make(EventRepository::class);
        $xlsxExportHelperService = app()->make(XlsxExportHelperService::class);
        $user = $eventRepository->findUserByEventId($this->eventId);

        $key = CacheKeys::EventTicketsByEventIdKey($this->eventId);
        $link = url('api/v1/events/' . $this->eventId.'/tickets/unique/export/');

        if ($this->status) {
            $link = url('api/v1/events/' . $this->eventId.'/tickets/unique/export/'.$this->status);
            $key = CacheKeys::EventTicketsByEventIdAndStatusKey($this->eventId,$this->status);
        }
        if (isset($this->eventTicketData['search']) && !empty($this->eventTicketData['search'])) {
            $link = url('api/v1/events/' . $this->eventId.'/tickets/unique/export/'.$this->status.'?search='.$this->eventTicketData['search']);
            $key = CacheKeys::EventTicketsByEventIdAndStatusWithSearchKey($this->eventId,$this->status,$this->eventTicketData['search']);
        }

        $tickets = $eventTicketRepository->allForCurrentAuthedUser(
            $this->eventId,
            $this->status,
            EventTicketTypes::UNIQUE,
            $this->eventTicketData['search'] ?? null,
            false
        );

        $data = (new FileEventTicketListResource($tickets))->toArray();
        $fileName = '/event_' . $this->eventId . '_' . time() . '_tickets.xlsx';

        $exportFile =  $xlsxExportHelperService->exportFile($data, $fileName);
        CacheServiceFacade::set(
            $key,
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
