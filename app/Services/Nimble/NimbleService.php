<?php

namespace App\Services\Nimble;

use App\Constants\CacheKeys;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Exceptions\NimbleNotFoundException;
use App\Exceptions\NimbleUnauthorizedException;
use App\Models\Stream;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Stream\StreamRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\WebSocket\WebSocketService;
use Carbon\Carbon;

class NimbleService
{
    public function __construct(
        public StreamRepository $streamRepository,
        public EventSessionRepository $eventSessionRepository,
        public WebSocketService $webSocketService
    ) {
    }

    public function createStream($key)
    {
        return [
            'output' => [
                'url' => '/out/' . $key . '/playlist.m3u8',
                'dvr_url' => '/out/' . $key . '/playlist_dvr.m3u8',
                'fullhd_url' => '/egr/' . $key . '/playlist.m3u8',
                'dvr_fullhd_url' => '/egr/' . $key . '/playlist_dvr.m3u8',
                'bucket' => ''
            ]
        ];
    }

    public function i32hash($str)
    {
        $h = 0;
        foreach (unpack('C*', $str) as &$p) {
            $h = (37 * $h + $p) % 4294967296;
        }
        return ($h - 2147483648);
    }

    public function checkStreamByData($data)
    {
        $stream = $this->streamRepository->findByKey($data["name"]);
        if (!$stream) {
            throw new NimbleNotFoundException();
        }

        if ($stream->input["rtmp_sharedkey"] !== $data["sharedkey"]) {
            throw new NimbleUnauthorizedException();
        }

        $now = Carbon::now()->timestamp;
        $date = Carbon::createFromTimeString($stream->start_at)->timestamp;

        // За час до старта и не позднее 12 часов от него
        if ($now < $date - 3600 || $now > $date + 12 * 3600) {
            throw new NimbleUnauthorizedException();
        }

        $start = $this->getStartTime($stream);

        // Не позднее 12 часов от фактического старта
        if ($start && $now > $start + 12 * 3600) {
            throw new NimbleUnauthorizedException();
        }

        return $stream;
    }

    public function publisherAuth($data)
    {
        $stream = $this->checkStreamByData($data);

        $streamData = [];
        if (is_null($stream->onair_at)) {
            $streamData["onair_at"] = Carbon::now()->format("Y-m-d H:i:s");
        }

        $streamData["last_auth_at"] = Carbon::now()->format("Y-m-d H:i:s");
        $streamData["is_onair"] = true;

        $this->streamRepository->updateByModel($streamData, $stream);

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

        return true;
    }

    public function publisherUpdate($data)
    {
        $stream = $this->checkStreamByData($data);

        $this->streamRepository->updateByModel(
            [
                "last_auth_at" => Carbon::now()->format("Y-m-d H:i:s"),
                "is_onair" => true
            ],
            $stream
        );

        CacheServiceFacade::tags([
            CacheKeys::eventSessionIdTag($stream['event_session_id']),
            CacheKeys::streamIdTag($stream['id'])
        ])
            ->flush();

        return true;
    }

    public function getPublisherRouteResolution($data)
    {
        $stream = $this->streamRepository->findByKey($data["name"]);
        if (!$stream) {
            throw new NimbleNotFoundException();
        }

        return $stream->is_fullhd_enabled ? '1080p' : '720p';
    }

    public function getStreamForS3($data)
    {
        $stream = $this->streamRepository->findByKey($data["name"]);
        if (!$stream) {
            return [
                "result" => "404",
                "start_date" => NULL,
                "current_date" => Carbon::now()->format("Y-m-d H:i:s")
            ];
        }

        return [
            "result" => $stream->is_vod ? "202" : '200',
            "start_date" => $stream->onair_at,
            "current_date" => Carbon::now()->format("Y-m-d H:i:s")
        ];
    }
    public function moveStreamToS3($data)
    {
        $stream = $this->streamRepository->findByKey($data["name"]);
        if (!$stream) {
            return [
                "result" => "404",
                "start_date" => NULL,
                "current_date" => Carbon::now()->format("Y-m-d H:i:s")
            ];
        }

        $already_moved = $stream->is_vod;

        if (!$already_moved) {
            $this->streamRepository->updateByModel(
                [
                    "is_vod" => 1,
                    "output" => [
                        'url' => '/' . $data["bucket"] . '/smil:' . $stream->key . '.smil/playlist.m3u8',
                        'dvr_url' => '/' . $data["bucket"] . '/smil:' . $stream->key . '.smil/playlist_dvr.m3u8',
                        'bucket' => $data["bucket"]
                    ]
                ],
                $stream
            );
        }

        CacheServiceFacade::tags([
            CacheKeys::eventSessionIdTag($stream['event_session_id']),
            CacheKeys::streamIdTag($stream['id'])
        ])
            ->flush();

        return [
            "result" => $already_moved ? "202" : '201',
            "start_date" => $stream->onair_at,
            "current_date" => Carbon::now()->format("Y-m-d H:i:s")
        ];
    }


    private function getStartTime(Stream $stream)
    {
        $eventSession = $this->eventSessionRepository->findByStreamId($stream->id);
        if ($eventSession->event_session_id) {
            $streams = $this->eventSessionRepository->pluckStream($eventSession->event_session_id);
        } else {
            $streams = $this->eventSessionRepository->pluckStream($eventSession->id);
        }
        $streams[] = $stream->id;

        $start = $this->streamRepository->findMinOnairAtByIds($streams);

        if ($start) $start = Carbon::createFromTimeString($start)->timestamp;

        return $start;
    }
}
