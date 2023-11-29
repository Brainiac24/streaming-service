<?php

namespace App\Repositories\MailingStatus;

use App\Constants\CacheKeys;
use App\Models\Contact;
use App\Models\MailingStatus;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class MailingStatusRepository extends BaseRepository
{
    public function __construct(public MailingStatus $mailingStatus)
    {
        parent::__construct($mailingStatus);
    }

}
