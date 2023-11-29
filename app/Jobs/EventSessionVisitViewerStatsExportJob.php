<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Chat\ChatExportResource;
use App\Http\Resources\EventSessionVisit\ExportEventSessionVisitUsersListResource;
use App\Http\Resources\EventSessionVisit\ExportEventSessionVisitViewersCountListResource;
use App\Models\EventDataCollectionTemplate;
use App\Models\EventSessionVisit;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Repositories\EventDataCollection\EventDataCollectionRepository;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Repositories\EventSessionVisit\EventSessionVisitRepository;
use App\Repositories\NimbleStat\NimbleStatRepository;
use App\Repositories\Stream\StreamRepository;
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
use Route;

class EventSessionVisitViewerStatsExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(public $streamId)
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
        $streamRepository = app()->make(StreamRepository::class);
        $eventSessionVisitRepository = app()->make(EventSessionVisitRepository::class);
        $nimbleStatRepository = app()->make(NimbleStatRepository::class);
        $xlsxExportHelperService = app()->make(XlsxExportHelperService::class);
        $user = $eventSessionVisitRepository->findUserByStreamId($this->streamId);
        $fileName = '';

        $event = $streamRepository->getEvent($this->streamId);
        $nimbleStats = $nimbleStatRepository->listByStreamIdAndEventDateEndAt($this->streamId, $event->end_at) ? : [];

        $data = (new ExportEventSessionVisitViewersCountListResource($nimbleStats))->toArray();
        $fileName = '/stream_' . $this->streamId . '_' . time() . '_viewers_count.xlsx';

        $exportFile =  $xlsxExportHelperService->exportFile($data, $fileName);

        CacheServiceFacade::set(
            CacheKeys::eventSessionVisitStatsByStreamIdKey($this->streamId),
            $exportFile,
            config('cache.ttl_ten_minute')
        );

        $socketData = new BaseJsonResource(
            data: [
                'filename' => 'viewer_stats_' . $this->streamId . '.xlsx',
                'link' => url('api/v1/event-session-visits/viewers-stats/export?streamId='.$this->streamId)
            ],
            mutation: WebSocketMutations::SOCK_FILE_EXPORT,
            scope: WebSocketScopes::CMS
        );

        $webSocketService->publish([$user['channel']], $socketData);
    }
}
