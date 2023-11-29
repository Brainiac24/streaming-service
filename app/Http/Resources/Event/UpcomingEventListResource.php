<?php

namespace App\Http\Resources\Event;

use App\Constants\ImagePlaceholders;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Role\RoleListResource;
use Illuminate\Support\Facades\Request;

class UpcomingEventListResource extends BaseJsonResource
{

    public function __construct($events)
    {

        $perPage = (int)Request::get('perPage', 12);
        $page = (int)Request::get('page', 1);

        $end = $page * $perPage;
        $start = $end - $perPage;

        $dataCount = 0;
        parent::__construct(data: $events);
        $this->data = [];
        $total = count($events);

        foreach ($events as &$event) {

            $dataCount++;
            if ($dataCount <= $start) {
                continue;
            }

            if ($dataCount > $end || $dataCount > $total) {
                break;
            }
            $eventSession = &$event['event_session'];

            if (isset($eventSession['roles']) && is_array($eventSession['roles'])) {
                $eventSession['roles'] = (new RoleListResource($eventSession['roles']))->toArray()['data'] ?? [];
            }

            if (empty($eventSession['stream']['cover_img_path'])) {
                $eventSession['stream']['cover_img_path'] = ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER;
            }

            $this->data[] = $event;
        }

        $lastPage = (int)($total / $perPage);
        $this->pagination = [
            'current_page' => $page,
            'last_page' =>  $lastPage > 0 ? $lastPage : 1,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }
}
