<?php

namespace App\Services\Ban;

use App\Exceptions\AccessForbiddenException;
use App\Repositories\Ban\BanRepository;

class BanService
{
    public function __construct(public BanRepository $banRepository)
    {
    }

    public function isUserBannedForEvent($eventId)
    {
        return $this->banRepository->findByEventIdForCurrentAuthedUser($eventId);
    }

    public function isUserBannedForEventAndFail($eventId)
    {
        if ($this->isUserBannedForEvent($eventId)) {
            throw new AccessForbiddenException();
        }
        return false;
    }
}