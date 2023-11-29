<?php

namespace App\Repositories\Contact;

use App\Constants\CacheKeys;
use App\Models\Contact;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use Auth;

class ContactRepository extends BaseRepository
{
    public function __construct(public Contact $contact)
    {
        parent::__construct($contact);
    }

    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->contact->currentAuthedUserByAuthedId()->findOrFail($id);
    }

    public function findByEventIdForCurrentAuthedUser($eventId)
    {
        return $this->contact->currentAuthedUserByAuthedIdAndEventId($eventId)->first();
    }

    public function allByEventIdAndContactGroupIdForJob($eventId, $contactGroupId = null)
    {
        return $this->contact
            ->join('contact_groups', 'contact_groups.id', '=', 'contacts.contact_group_id')
            ->where('contact_groups.event_id', $eventId)
            ->when($contactGroupId != null, function ($query) use ($contactGroupId) {
                $query->where('contacts.contact_group_id', $contactGroupId);
            })
            ->get([
                'contacts.*',
                'contact_groups.name as contact_group_name',
            ]);
    }

    public function allByEventIdAndContactGroupId($eventId, $contactGroupId = null)
    {
        return $this->contact
            ->join('contact_groups', 'contact_groups.id', '=', 'contacts.contact_group_id')
            ->join('events',  'events.id', '=', 'contact_groups.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            })
            ->where('contact_groups.event_id', $eventId)
            ->when($contactGroupId != null, function ($query) use ($contactGroupId) {
                $query->where('contacts.contact_group_id', $contactGroupId);
            })
            ->get([
                'contacts.*',
                'contact_groups.name as contact_group_name',
            ]);
    }

    public function allByContactGroupId($contactGroupId)
    {
        return $this->contact
            ->join('contact_groups', 'contact_groups.id', '=', 'contacts.contact_group_id')
            ->join('events',  'events.id', '=', 'contact_groups.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            })
            ->leftJoin('event_tickets', 'event_tickets.id', 'contacts.event_ticket_id')
            ->where('contacts.contact_group_id', $contactGroupId)
            ->get([
                'contacts.*',
                'contact_groups.event_id',
                'contact_groups.name as contact_group_name',
                'event_tickets.ticket'
            ]);
    }

    public function allWithEventId($email)
    {
        $cacheKey = CacheKeys::contactsWithEventIdKey();

        $contacts = CacheServiceFacade::get($cacheKey);

        if (!$contacts) {
            $contacts = $this->contact
                ->join('contact_groups', 'contact_groups.id', '=', 'contacts.contact_group_id')
                ->leftJoin('event_tickets', 'event_tickets.id', '=', 'contacts.event_ticket_id')
                ->get([
                    'contacts.*',
                    'contact_groups.event_id',
                    'event_tickets.event_ticket_status_id'
                ]);

            if (!empty($contacts?->toArray())) {
                CacheServiceFacade::set($cacheKey, $contacts->toArray(), config('cache.ttl_one_hour'));
            }

            $contacts = $contacts->toArray();
        }

        $result = [];

        foreach ($contacts as $key => $val) {
            if ($val['email'] == $email) {
                $result[] = $val;
            }
        }

        return $result;
    }

    public function emailListByContactGroupId($contactGroupId)
    {
        return $this->contact
            ->where('contacts.contact_group_id', $contactGroupId)
            ->get([
                'contacts.email'
            ]);
    }
}
