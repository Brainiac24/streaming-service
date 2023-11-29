<?php

namespace App\Services\EventDataCollectionTemplate;

use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use Auth;

class EventDataCollectionTemplateService
{

    public function __construct(public EventDataCollectionTemplateRepository $eventDataCollectionTemplateRepository)
    {
    }

    public function getTemplateWithUserDataByEventId($eventId)
    {
        $eventDataTemplates = $this->eventDataCollectionTemplateRepository->getTemplatesByEventId($eventId)?->toArray();

        if (empty($eventDataTemplates)) {
            return $eventDataTemplates;
        }

        $user = null;
        if (Auth::isUserHasToken()) {
            $user = Auth::user();
        }

        foreach ($eventDataTemplates as &$eventDataTemplate) {
            $eventDataTemplate['value'] = $this->fillUserFields($eventDataTemplate['name'], $user);
        }

        return $eventDataTemplates;
    }

    public function fillUserFields($name, $user)
    {
        if (!$user) {
            return '';
        }

        switch ($name) {
            case 'country':
                return $user->country;
            case 'region':
                return $user->region;
            case 'city':
                return $user->city;
            case 'work_scope':
                return $user->work_scope;
            case 'work_company':
                return $user->work_company;
            case 'work_division':
                return $user->work_division;
            case 'work_position':
                return $user->work_position;
            case 'education':
                return $user->education;
            default:
                return '';
        }
    }
}
