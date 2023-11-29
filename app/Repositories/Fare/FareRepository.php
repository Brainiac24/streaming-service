<?php

namespace App\Repositories\Fare;

use App\Constants\CacheKeys;
use App\Constants\FareTypes;
use App\Models\Fare;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class FareRepository extends BaseRepository
{
    public function __construct(public Fare $fare)
    {
        parent::__construct($fare);
    }

    public function allWithFareType()
    {
        return $this->fare->with('fareType')->get();
    }

    public function getFirstExtraFare()
    {
        return $this->fare->where('fare_type_id', FareTypes::EXTRA)->first();
    }

    public function getFareByEventSessionId($eventSessionId)
    {
        $cacheKey = CacheKeys::fareByEventSessionIdKey($eventSessionId);

        $fare = CacheServiceFacade::get($cacheKey);

        if (!$fare) {
            $fare = $this->fare
                ->join('event_sessions', function ($query) use ($eventSessionId) {
                    $query
                        ->on('event_sessions.fare_id', '=', 'fares.id')
                        ->where('event_sessions.id', '=', $eventSessionId);
                })
                ->select('fares.*')
                ->first();

            if (!$fare) {
                CacheServiceFacade::tags([
                    CacheKeys::eventSessionIdTag($eventSessionId),
                    CacheKeys::fareIdTag($fare['id'])
                ])
                    ->set($cacheKey, $fare, config('cache.ttl'));
            }
        }

        return $fare;
    }
}
