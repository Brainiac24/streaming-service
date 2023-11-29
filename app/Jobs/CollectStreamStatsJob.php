<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\EventSessionVisit\EventSessionVisitRepository;
use App\Repositories\NimbleStat\NimbleStatRepository;
use App\Repositories\Stream\StreamRepository;
use App\Repositories\StreamStat\StreamStatRepository;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Fare\FareService;
use App\Services\WebSocket\WebSocketService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CollectStreamStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public StreamStatRepository $streamStatRepository,
        public StreamRepository $streamRepository,
        public NimbleStatRepository $nimbleStatRepository,
        public WebSocketService $webSocketService,
        public EventSessionRepository $eventSessionRepository,
        public UserRepository $userRepository,
        public FareService $fareService,
        public EventSessionVisitRepository $eventSessionVisitRepository
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('CollectStreamStatsJob dispatched!');

        $streams = $this->streamRepository->getByIsOnairAndStartAt(true, Carbon::now()->subHours(24)->format("Y-m-d H:i:s"));

        foreach ($streams as $stream) {
            $connectedUserIds = $this->streamStatRepository->getStreamConnectedUsers($stream->id);

            $connectedCount = count($connectedUserIds);

            $stream->update(["user_connected_count" => $connectedCount]);

            CacheServiceFacade::tags([
                CacheKeys::eventSessionIdTag($stream['event_session_id']),
                CacheKeys::streamIdTag($stream['id'])
            ])
                ->flush();

            if ($connectedCount) {
                $this->nimbleStatRepository->insert([
                    'stream_id' => $stream->id,
                    'connected_count' => $connectedCount,
                    'created_at' =>  Carbon::now()
                ]);
            }

            $socketData = [
                "scope" => WebSocketScopes::EVENT,
                "mutation" => WebSocketMutations::SOCK_SET_STREAM_STATS,
                "data" => [
                    "stream_id" => $stream->id,
                    "user_connected_count" => $connectedCount
                ]
            ];

            $this->webSocketService->publish($stream['private_channel'], $socketData);

            $socketData["scope"] = WebSocketScopes::CMS;

            $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($stream['event_session_id']);
            $users = $this->userRepository->findAdminsAndModeratorsByAccessGroupId($accessGroupId);
            $this->webSocketService->publish($users?->pluck('channel'), $socketData);

            $eventSession = $this->eventSessionRepository->findByStreamId($stream['id']);
            if (!empty($eventSession['event_session_id'])) {
                $parentEventSession = $this->eventSessionRepository->findById($eventSession['event_session_id']);
                $eventSession = $this->eventSessionRepository->findByStreamId($parentEventSession['stream_id']);
            }
            $userConnectedExceededCount = $this->fareService->userConnectedExceededCount($eventSession);

            if ($userConnectedExceededCount > 0) {
                $uniqueUsersChannel = $this->eventSessionVisitRepository->getUniqueUsersChannelByEventSessionAndLimit(
                    $eventSession['id'],
                    $userConnectedExceededCount,
                    $connectedUserIds
                );

                $socketLimitData = [
                    "scope" => WebSocketScopes::EVENT,
                    "mutation" => WebSocketMutations::SOCK_KICK_USER,
                    "data" => [
                        "event_session_id" => $eventSession['id'],
                        "is_user_limit_error" => true
                    ]
                ];

                $this->webSocketService->publish($uniqueUsersChannel, $socketLimitData);

                $calculatedUserCount = $connectedCount - $userConnectedExceededCount;
                $stream->update(["user_connected_count" => ($calculatedUserCount < 0 ? 0 : $calculatedUserCount)]);
            }
        }

        return;
    }
}
