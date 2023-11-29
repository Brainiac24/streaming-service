<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Stream\StreamRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\WebSocket\WebSocketService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class UpdateStreamStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public StreamRepository $streamRepository, 
        public EventSessionRepository $eventSessionRepository,
        public WebSocketService $webSocketService
        )
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

        $streams = $this->streamRepository->getByIsOnairAndLastAuthAt(true, Carbon::now()->subMinutes(30));

        foreach ($streams as $stream) {
            Log::info('UpdateStreamStatusJob dispatched!');

            $this->streamRepository->updateByModel(['is_onair' => false], $stream);

            CacheServiceFacade::tags([
                CacheKeys::eventSessionIdTag($stream['event_session_id']),
                CacheKeys::streamIdTag($stream['id'])
            ])
                ->flush();

            $eventSession = $this->eventSessionRepository->findByStreamId($stream['id']);

            $socketData = [
                "scope" => WebSocketScopes::EVENT,
                "mutation" => WebSocketMutations::SOCK_SESSION_STREAM_ONAIR,
                "data" => [
                    "session_id" => $eventSession['id'],
                    "is_onair" => $stream["is_onair"],
                    "onair_at" => $stream["onair_at"]
                ]
            ];

            $this->webSocketService->publish([$eventSession['channel'], $eventSession['private_channel']], $socketData);
        }
    }
}
