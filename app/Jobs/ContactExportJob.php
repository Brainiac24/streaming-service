<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Chat\ChatExportResource;
use App\Http\Resources\Contact\ContactListResource;
use App\Http\Resources\EventSessionVisit\ExportEventSessionVisitUsersListResource;
use App\Models\EventDataCollectionTemplate;
use App\Models\EventSessionVisit;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Repositories\Contact\ContactRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventDataCollection\EventDataCollectionRepository;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Repositories\EventSessionVisit\EventSessionVisitRepository;
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

class ContactExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(public $eventId, public $requestData)
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
        $contactRepository = app()->make(ContactRepository::class);
        $eventRepository = app()->make(EventRepository::class);
        $xlsxExportHelperService = app()->make(XlsxExportHelperService::class);

        $user = $eventRepository->findUserByEventId($this->eventId);
        $fileName = '';

        $contactGroupId = null;
        $key = CacheKeys::contactListByEventIdKey($this->eventId);
        $link = url('api/v1/contacts/export/event/' . $this->eventId);
        if (isset($this->requestData['contact_group_id']) && !empty($this->requestData['contact_group_id'])) {
            $contactGroupId = $this->requestData['contact_group_id'];
            $link = url('api/v1/contacts/export/event/' . $this->eventId.'?contact_group_id='.$this->requestData['contact_group_id']);
            $key = CacheKeys::contactListByEventIdAndContactGroupIdKey($this->eventId,$this->requestData['contact_group_id']);
        }

        $contactList = $contactRepository->allByEventIdAndContactGroupId($this->eventId, $contactGroupId);

        $data = (new ContactListResource($contactList))->toArray();
        $fileName = '/event_' . $this->eventId . '_' . time() . '_contact_list.xlsx';

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
