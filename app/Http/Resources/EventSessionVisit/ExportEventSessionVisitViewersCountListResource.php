<?php

namespace App\Http\Resources\EventSessionVisit;

use App\Http\Resources\BaseJsonResource;

class ExportEventSessionVisitViewersCountListResource extends BaseJsonResource
{
    public function __construct($nimbleStats)
    {
        $data[] = [
            'date' => __('Time'),
            'viewers_count' => __('Viewers'),
        ];
        foreach ($nimbleStats as $nimbleStat) {
            if ((int)($nimbleStat->created_at->minute) % 5 == 0) {
                $data[] = [
                    'date' => $nimbleStat->created_at->format('d.m.Y H:i'),
                    'viewers_count' => $nimbleStat->connected_count
                ];
            }
        }
        $this->data = $data;
    }
}
