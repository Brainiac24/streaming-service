<?php

namespace App\Repositories\Stream;

use App\Constants\CacheKeys;
use App\Constants\EventSessionStatuses;
use App\Models\Stream;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class StreamRepository extends BaseRepository
{
    public function __construct(public Stream $stream)
    {
        parent::__construct($stream);
    }

    public function findMinOnairAtByIds($ids)
    {
        return $this->stream
            ->whereIn("id", $ids)
            ->min('onair_at');
    }

    public function findByKey($key)
    {
        return $this->stream
            ->where('key', $key)
            ->first();
    }

    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->stream
            ->currentAuthedUser()
            ->findOrFail($id, [
                'streams.*',
                'event_sessions.event_session_id as parent_event_session_id'
            ]);
    }

    public function findEventSessionIdById($id)
    {
        return CacheServiceFacade::remember(CacheKeys::eventSessionsIdByStreamIdKey($id), config('cache.ttl'), function () use ($id) {
            return $this->stream
                ->join('event_sessions',  'event_sessions.stream_id',  'streams.id')
                ->findOrFail($id, [
                    'event_sessions.event_session_id as event_session_id'
                ])
                ?->event_session_id;
        });
    }

    public function findByIdForCurrentAuthedUserByEventSessionId($eventSessionId)
    {
        return $this->stream
            ->currentAuthedUserByEventSessionIdNotParent($eventSessionId)
            ->first([
                'streams.*',
            ]);
    }

    public function findByIdForCurrentAuthedUserWithFares($id)
    {
        return $this->stream
            ->currentAuthedUser()
            ->join('fares',  'fares.id',  'event_sessions.fare_id')
            ->leftJoin('event_sessions as parent_event_sessions',  'parent_event_sessions.id',  'event_sessions.event_session_id')
            ->leftJoin('streams as parent_streams',  'parent_streams.id',  'parent_event_sessions.stream_id')
            ->findOrFail($id, [
                'streams.*',
                'event_sessions.event_session_id as parent_event_session_id',
                'parent_streams.start_at as parent_stream_start_at',
                'event_sessions.id as event_session_id',
                'fares.config_json as fares_config_json'
            ]);
    }

    public function listStreamByEventSessionIdForCurrentAuthedUser($eventSessionId)
    {
        return $this->stream
            ->currentAuthedUserByEventSessionId($eventSessionId)
            ->get([
                'streams.*',
                'event_sessions.id as event_session_id'
            ]);
    }

    public function getEvent($streamId)
    {
        return $this->stream
            ->currentAuthedUser()
            ->where('streams.id', $streamId)
            ->firstOrFail([
                'events.*'
            ]);
    }

    public function getByIsOnairAndLastAuthAt($isOnair, $lastAuthAt)
    {
        return $this->stream
            ->where([
                ["is_onair", $isOnair],
                ["last_auth_at", "<", $lastAuthAt]
            ])->get();
    }

    public function getByIsOnairAndStartAt($isOnair, $onair_at)
    {
        return $this->stream
            ->join('event_sessions',  'event_sessions.stream_id',  'streams.id')
            ->where("onair_at", ">", $onair_at)
            ->get([
                'streams.*',
                'event_sessions.id as event_session_id',
                'event_sessions.private_channel'
            ]);
    }

    public function getIsDefaultMailingListByEventId($eventId)
    {
        return $this->stream
            ->join('event_sessions', function ($join) {
                $join->on('event_sessions.stream_id', 'streams.id')
                    ->where("event_sessions.event_session_status_id", EventSessionStatuses::ACTIVE);
            })
            ->join('events', function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'event_sessions.event_id')
                    ->where('events.id', '=', $eventId);
            })
            ->join('mailings', function ($join) {
                $join
                    ->on('mailings.event_id', '=', 'events.id')
                    ->where('is_default', true);
            })
            ->get([
                'mailings.*'
            ]);
    }

    public function getEventByStreamId($streamId)
    {
        return $this->stream
            ->join('event_sessions', function ($join) {
                $join->on('event_sessions.stream_id', 'streams.id')
                    ->where("event_sessions.event_session_status_id", EventSessionStatuses::ACTIVE);
            })
            ->join('events', 'events.id', '=', 'event_sessions.event_id')
            ->where('streams.id', $streamId)
            ->first([
                'events.*'
            ]);
    }

    public function getNearestStreamByEventId($eventId)
    {
        return $this->stream
            ->join('event_sessions', function ($join) {
                $join
                    ->on('event_sessions.stream_id', 'streams.id')
                    ->where("event_sessions.event_session_status_id", EventSessionStatuses::ACTIVE);
            })
            ->join('events', function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'event_sessions.event_id')
                    ->where('events.id', '=', $eventId);
            })
            ->orderBy('streams.start_at')
            ->first([
                'streams.*'
            ]);
    }
}
