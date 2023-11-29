<?php

namespace App\Services\EventSessionVisit;

use App\Constants\CacheKeys;
use App\Constants\StatusCodes;
use App\Exceptions\NotFoundException;
use App\Http\Resources\BaseJsonResource;
use App\Jobs\EventSessionVisitExportJob;
use App\Jobs\EventSessionVisitViewerStatsExportJob;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\EventSessionVisit\EventSessionVisitRepository;
use App\Repositories\NimbleStat\NimbleStatRepository;
use App\Repositories\Stream\StreamRepository;
use App\Services\Helper\XlsxExportHelperService;
use Cache;
use Illuminate\Support\Facades\Auth;
use Response;
use Storage;

class EventSessionVisitService
{

    public function __construct(
        public EventSessionVisitRepository $eventSessionVisitRepository,
        public EventSessionRepository $eventSessionRepository,
        public EventDataCollectionTemplateRepository $eventDataCollectionTemplateRepository,
        public NimbleStatRepository $nimbleStatRepository,
        public StreamRepository $streamRepository,
        public XlsxExportHelperService $xlsxExportHelperService
    ) {
    }

    public function exportUserList($eventSessionId)
    {
        if (!$this->eventSessionVisitRepository->findByEventSessionIdForCurrentAuthedUser($eventSessionId))
        {
            throw new NotFoundException();
        }

        $user = $this->eventSessionVisitRepository->findUserByEventSessionId($eventSessionId);
        if (!$user) {
            return Response::apiError(
                new BaseJsonResource(
                    code: StatusCodes::NOT_FOUND_ERROR,
                    message: __('Not found!')
                ),
                404
            );
        }
        $eventSessionVisitFile = Cache::get(CacheKeys::eventSessionVisitByEventSessionIdKey($eventSessionId));

        if (!$eventSessionVisitFile) {
            EventSessionVisitExportJob::dispatch($eventSessionId);
            return Response::apiSuccess(
                new BaseJsonResource(
                    code: StatusCodes::IN_PROCESS,
                    message: __('In process'),
                )
            );
        }

        return Response::apiSuccess(
            new BaseJsonResource(
                code: StatusCodes::SUCCESS,
                message: __('Success'),
                data: [
                    'link' => url('api/v1/event-session-visits/users-stats/export?eventSessionId='.$eventSessionId)
                ]
            )
        );
    }

    public function downloadUserList($eventSessionId){

        if (!$this->eventSessionVisitRepository->findByEventSessionIdForCurrentAuthedUser($eventSessionId)) {
            throw new NotFoundException();
        }

        $eventSessionVisitFile = Cache::get(CacheKeys::eventSessionVisitByEventSessionIdKey($eventSessionId));
        if (!$eventSessionVisitFile) {
            return Response::apiError(
                new BaseJsonResource(
                    code: StatusCodes::NOT_FOUND_ERROR,
                    message: __('Not found!')
                ),
                404
            );
        }
        return Storage::disk("local")->download('local/xlsx/' . $eventSessionVisitFile, headers: [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Length' => null,
        ]);
    }

    public function create($eventSessionId)
    {
        $data['event_session_id'] = $eventSessionId;
        $data['user_id'] = Auth::id();
        $data['useragent'] = request()->userAgent();
        $data['ip'] = request()->ip();
        $data['url'] = request()->fullUrl();
        $data['source'] = request()->header('referer');

        $this->eventSessionVisitRepository->create($data);
    }

    public function getStreamStats($eventSessionId, $streamId)
    {
        $maxConnectedCount = $this->nimbleStatRepository->maxConnected($streamId);
        $uniqueClientsCount = $this->eventSessionVisitRepository->uniqueClientsCount($eventSessionId);

        return [
            "max_connected_count" => (int)$maxConnectedCount,
            "unique_clients_count" => $uniqueClientsCount
        ];
    }

    public function getViewersStats($streamId)
    {

        if (!$this->eventSessionVisitRepository->findByStreamIdForCurrentAuthedUser($streamId)) {
            throw new NotFoundException();
        }

        $eventSessionVisitStatsFile = Cache::get(CacheKeys::eventSessionVisitStatsByStreamIdKey($streamId));

        if (!$eventSessionVisitStatsFile) {
            EventSessionVisitViewerStatsExportJob::dispatch($streamId);
            return Response::apiSuccess(
                new BaseJsonResource(
                    code: StatusCodes::IN_PROCESS,
                    message: __('In process'),
                )
            );
        }

        return Response::apiSuccess(
            new BaseJsonResource(
                code: StatusCodes::SUCCESS,
                message: __('Success'),
                data: [
                    'filename' => 'viewer_stats_' . $streamId . '.xlsx',
                    'link' => url('api/v1/event-session-visits/viewers-stats/export?streamId='.$streamId)
                ]
            )
        );
    }

    public function downloadViewersStats($streamId){

        if (!$this->eventSessionVisitRepository->findByStreamIdForCurrentAuthedUser($streamId)) {
            throw new NotFoundException();
        }

        $eventSessionVisitStatsFile = Cache::get(CacheKeys::eventSessionVisitStatsByStreamIdKey($streamId));
        if (!$eventSessionVisitStatsFile) {
            return Response::apiError(
                new BaseJsonResource(
                    code: StatusCodes::NOT_FOUND_ERROR,
                    message: __('Not found!')
                ),
                404
            );
        }
        return Storage::disk("local")->download('local/xlsx/' . $eventSessionVisitStatsFile, headers: [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Length' => null,
        ]);
    }

}
