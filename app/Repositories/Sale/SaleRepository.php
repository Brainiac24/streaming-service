<?php

namespace App\Repositories\Sale;

use App\Constants\CacheKeys;
use App\Models\Sale;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class SaleRepository extends BaseRepository
{
    public function __construct(public Sale $sale)
    {
        parent::__construct($sale);
    }

    public function listByEventSessionId($eventSessionId)
    {
        $cacheKey = CacheKeys::salesByEventSessionIdKey($eventSessionId);
        $sales = CacheServiceFacade::get($cacheKey);
        if (!$sales) {
            $sales = $this->sale
                ->where('event_session_id', '=', $eventSessionId)
                ->orderBy('sort', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            if ($sales->isNotEmpty()) {
                $saleIds = [];
                foreach ($sales as $sale) {
                    if (!in_array($sale['id'], $saleIds)) {
                        $saleIds[] = $sale['id'];
                    }
                }

                CacheServiceFacade::tags([
                    CacheKeys::eventSessionIdTag($eventSessionId),
                    ...CacheKeys::setSaleIdTags($saleIds)
                ])
                    ->set($cacheKey, $sales, config('cache.ttl'));
            }
        }

        return $sales;
    }

    public function accessGroupIdBySaleId($saleId)
    {
        return CacheServiceFacade::remember(CacheKeys::accessGroupBySaleIdKey($saleId), config('cache.ttl'), function () use ($saleId) {
            return $this->sale
                ->join('event_sessions', 'event_sessions.id', '=', 'sales.event_session_id')
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->where('sales.id', '=', $saleId)
                ->first(['events.access_group_id'])
                ->access_group_id;
        });
    }
}
