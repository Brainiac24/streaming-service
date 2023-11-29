<?php

namespace App\Repositories\ContactGroup;

use App\Models\ContactGroup;
use App\Repositories\BaseRepository;

class ContactGroupRepository extends BaseRepository
{
    public function __construct(public ContactGroup $contactGroup)
    {
        parent::__construct($contactGroup);
    }

    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->model->currentAuthedUserByAuthedId()->findOrFail($id, [
            'contact_groups.*'
        ]);
    }

    public function findByEventIdAndIsCommonTrueForCurrentAuthedUser($eventId)
    {
        return $this->model
            ->currentAuthedUserByAuthedId()
            ->where('contact_groups.event_id', $eventId)
            ->where('contact_groups.is_common', true)
            ->first([
                'contact_groups.*'
            ]);
    }

    public function getByEventIdForCurrentAuthedUser($eventId)
    {
        return $this->contactGroup
            ->currentAuthedUserByAuthedId()
            ->where('contact_groups.event_id', $eventId)
            ->get([
                'contact_groups.*',
            ]);
    }
}
