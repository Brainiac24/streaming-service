<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\EventSessionVisit\ExportEventSessionVisitUsersListResource;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Repositories\EventSessionVisit\EventSessionVisitRepository;
use App\Services\Cache\CacheServiceFacade;
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

class EventSessionVisitExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(public $eventSessionId)
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
        $eventDataCollectionTemplateRepository = app()->make(EventDataCollectionTemplateRepository::class);
        $eventSessionVisitRepository = app()->make(EventSessionVisitRepository::class);
        $xlsxExportHelperService = app()->make(XlsxExportHelperService::class);

        $user = $eventSessionVisitRepository->findUserByEventSessionId($this->eventSessionId);
        $fileName = '';

        $eventDataCollectionTemplate = $eventDataCollectionTemplateRepository->getTemplatesByEventSessionId($this->eventSessionId);

        $eventSessionVisits = $eventSessionVisitRepository->getUserListForExportWithUsersByEventSessionId($this->eventSessionId);
        $data = (new ExportEventSessionVisitUsersListResource($eventSessionVisits, $eventDataCollectionTemplate))->toArray();
        $fileName = '/event_session_visits_' . $this->eventSessionId . '_' . time() . '_user_list.xlsx';

        $exportFile =  $xlsxExportHelperService->exportFile($data, $fileName);
        CacheServiceFacade::set(
            CacheKeys::eventSessionVisitByEventSessionIdKey($this->eventSessionId),
            $exportFile,
            config('cache.ttl_ten_minute')
        );

        $socketData = new BaseJsonResource(
            data: [
                'link' => url('api/v1/event-session-visits/users-stats/export?eventSessionId=' . $this->eventSessionId)
            ],
            mutation: WebSocketMutations::SOCK_FILE_EXPORT,
            scope: WebSocketScopes::CMS
        );
        $webSocketService->publish([$user['channel']], $socketData);
    }
}
