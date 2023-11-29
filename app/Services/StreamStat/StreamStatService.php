<?php

namespace App\Services\StreamStat;

use App\Repositories\StreamStat\StreamStatRepository;
use Auth;

class StreamStatService
{
    public function __construct(public StreamStatRepository $streamStatRepository)
    {
    }
    public function createWithStreamId($data)
    {
        if (isset($data['data'])) {
            $data['data_json'] = $data['data'];
            unset($data['data']);
        }
        $data['user_id'] = Auth::id();
        $data['ip'] = request()->ip();
        $data['useragent'] = request()->userAgent();

        try {
            $streamStat = $this->streamStatRepository->createWithTableNamePostfix($data, $data['stream_id']);
        } catch (\Illuminate\Database\QueryException $e) {
            if (strpos($e->getMessage(), 'Base table or view not found')) {
                $this->streamStatRepository->createTableWithStreamId($data['stream_id']);
                $streamStat = $this->streamStatRepository->create($data);
            } else {
                throw $e;
            }
        }

        return $streamStat;
    }
}
