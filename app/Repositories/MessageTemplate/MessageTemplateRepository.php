<?php

namespace App\Repositories\MessageTemplate;


use App\Models\MessageTemplate;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class MessageTemplateRepository extends BaseRepository
{
    public function __construct(public MessageTemplate $messageTemplate)
    {
        parent::__construct($messageTemplate);
    }

}
