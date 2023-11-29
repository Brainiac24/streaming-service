<?php

namespace App\Services\Fare;

use App\Exceptions\BusinessLogicException;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Fare\FareRepository;

class FareService
{
    public function __construct(
        public FareRepository $fareRepository,
        public EventSessionRepository $eventSessionRepository
    ) {
    }
    function checkFareUserConnectedCountIsNotExceededAndFail($eventSession)
    {
        if ($this->userConnectedExceededCount($eventSession) + 1 > 0) {
            throw new BusinessLogicException("Access to this broadcast is now closed. Contact the event organizer!");
        }
    }

    function userConnectedExceededCount($eventSession)
    {
        $fare = $this->fareRepository->getFareByEventSessionId($eventSession['id']);

        $fareMaxAllowedViewersCount = intval($fare['config_json']['viewers_count']);

        $streamUserConnectedCount = intval($eventSession['stream_user_connected_count']);

        $childEventSessions = $this->eventSessionRepository->allChildByEventSessionId($eventSession['id'])?->toArray();

        if (!empty($childEventSessions)) {
            foreach ($childEventSessions as $childEventSession) {
                $streamUserConnectedCount += intval($childEventSession['stream_user_connected_count']);
            }
        }

        return $streamUserConnectedCount - $fareMaxAllowedViewersCount;
    }
}
