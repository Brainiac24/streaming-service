<?php

namespace App\Repositories\EventSessionVisit;

use App\Constants\Roles;
use App\Models\EventSessionVisit;
use App\Repositories\BaseRepository;

class EventSessionVisitRepository extends BaseRepository
{
    public function __construct(public EventSessionVisit $eventSessionVisit)
    {
        parent::__construct($eventSessionVisit);
    }

    public function getUserListForExportWithUsersByEventSessionId($eventSessionId)
    {
        return $this->eventSessionVisit
            ->currentAuthedUserByEventSessionId($eventSessionId)
            ->join('users', 'users.id', '=', 'event_session_visits.user_id')
            ->leftJoin('event_data_collections', function ($join) {
                $join
                    ->on('event_data_collections.user_id', '=', 'users.id')
                    ->on('event_data_collections.event_id', '=', 'events.id');
            })
            ->leftJoin('event_data_collection_templates', 'event_data_collections.event_data_collection_template_id', '=', 'event_data_collection_templates.id')
            ->get([
                'users.id',
                'users.email',
                'users.name',
                'users.lastname',
                'users.phone',
                'event_session_visits.source',
                'event_session_visits.ip',
                'event_data_collections.value as event_data_collection_value',
                'event_data_collection_templates.name as event_data_collection_template_name',
                'event_data_collection_templates.label as event_data_collection_template_label',
                'events.id as event_id'
            ]);
    }

    public function uniqueClientsCount($eventSessionId)
    {
        return $this->eventSessionVisit->where('event_session_id', $eventSessionId)->groupBy('user_id')->get('id')->count();
    }

    public function getUniqueUsersChannelByEventSessionAndLimit($eventSessionId, $limit, $connectedUserIds)
    {
        return $this->eventSessionVisit
            ->join('users', 'users.id', '=', 'event_session_visits.user_id')
            ->join('event_sessions', function ($join) use ($eventSessionId) {
                $join
                    ->on('event_sessions.id', '=', 'event_session_visits.event_session_id')
                    ->where('event_session_visits.event_session_id', $eventSessionId);
            })
            ->join('events', 'events.id', '=', 'event_sessions.event_id')
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', function ($join) {
                $join
                    ->on('role_user.access_group_id', '=', 'access_groups.id')
                    ->on('role_user.user_id', '=', 'users.id')
                    ->where('role_user.role_id', '!=', Roles::ADMIN)
                    ->where('role_user.role_id', '!=', Roles::MODERATOR);
            })
            ->whereIn('users.id', $connectedUserIds)
            ->groupBy(['event_session_visits.user_id'])
            ->orderBy('event_session_visits.id', 'desc')
            ->limit($limit)
            ->pluck('users.channel');
    }

    public function findUserByEventSessionId($eventSessionId)
    {
        return $this->eventSessionVisit
            ->join('event_sessions', 'event_sessions.id', '=', 'event_session_visits.event_session_id')
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('projects',  'projects.id', '=', 'events.project_id')
            ->join('users',  'users.id', '=', 'projects.user_id')
            ->where('event_session_visits.event_session_id', '=', $eventSessionId)
            ->first(['users.*']);
    }

    public function findUserByStreamId($streamId)
    {
        return $this->eventSessionVisit
            ->join('event_sessions', 'event_sessions.id', '=', 'event_session_visits.event_session_id')
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('projects',  'projects.id', '=', 'events.project_id')
            ->join('users',  'users.id', '=', 'projects.user_id')
            ->where('event_sessions.stream_id', '=', $streamId)
            ->first(['users.*']);
    }

    public function findByEventSessionIdForCurrentAuthedUser($eventSessionId)
    {
        return $this->eventSessionVisit
            ->currentAuthedUserByEventSessionId($eventSessionId)
            ->first(['event_session_visits.*']);
    }

    public function findByStreamIdForCurrentAuthedUser($streamId)
    {
        return $this->eventSessionVisit
            ->currentAuthedUserByEventStreamId($streamId)
            ->first(['event_session_visits.*']);
    }
}
