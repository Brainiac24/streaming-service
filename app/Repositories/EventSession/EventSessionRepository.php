<?php

namespace App\Repositories\EventSession;

use App\Constants\CacheKeys;
use App\Constants\EventSessionStatuses;
use App\Constants\Roles;
use App\Models\EventSession;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class EventSessionRepository extends BaseRepository
{
    public function __construct(public EventSession $eventSession)
    {
        parent::__construct($eventSession);
    }

    public function pluckCode($evenId)
    {
        return $this->eventSession->where("event_id", $evenId)->pluck("code")->toArray();
    }

    public function pluckStream($eventSessionId)
    {
        return $this->eventSession->where("event_session_id", $eventSessionId)->get()->pluck("stream");
    }

    public function findByEventId($eventId)
    {
        return  $this->eventSession->currentAuthedUser($eventId)->get('event_sessions.*');
    }

    public function findByKey($key)
    {
        return $this->eventSession->where('key', $key)->firstOrFail();
    }

    public function findByStreamId($streamId)
    {
        $eventSession = CacheServiceFacade::get(CacheKeys::eventSessionByStreamIdKey($streamId));
        if (!$eventSession) {
            $eventSession = $this->eventSession
                ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
                ->where('stream_id', $streamId)
                ->firstOrFail([
                    'event_sessions.*',
                    'streams.user_connected_count as stream_user_connected_count'
                ]);

            CacheServiceFacade::tags(CacheKeys::eventSessionIdTag($eventSession['id']))
                ->set(
                    CacheKeys::eventSessionByStreamIdKey($streamId),
                    $eventSession,
                    config('cache.ttl')
                );
        }

        return $eventSession;
    }

    public function findByKeyWithEvent($key)
    {
        $cacheKey = CacheKeys::eventSessionByKey($key);
        $eventSession = CacheServiceFacade::get($cacheKey);
        if (!$eventSession) {
            $eventSession = $this->eventSession
                ->join('events', 'events.id', '=', 'event_sessions.event_id')
                ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
                ->where('event_sessions.key', $key)
                ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE)
                ->firstOrFail([
                    'event_sessions.*',
                    'streams.id as stream_id',
                    'streams.cover_img_path as stream_cover_img_path',
                    'streams.user_connected_count as stream_user_connected_count',
                    'events.cover_img_path as event_cover_img_path',
                    'events.is_unique_ticket_enabled',
                    'events.is_multi_ticket_enabled',
                    'events.is_data_collection_enabled',
                ]);
            CacheServiceFacade::tags([
                CacheKeys::eventIdTag($eventSession['event_id']),
                CacheKeys::eventSessionIdTag($eventSession['id']),
                CacheKeys::streamIdTag($eventSession['stream_id'])
            ])
                ->set($cacheKey, $eventSession, config('cache.ttl'));
        }



        return $eventSession;
    }

    public function allWithChildByEventIdForCurrentAuthedUser($eventId)
    {
        return $this->eventSession
            ->currentAuthedUser($eventId)
            ->whereNull('event_session_id')
            ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
            ->with([
                'children' => function ($query) {
                    return $query
                        ->join('events', 'events.id', 'event_sessions.event_id')
                        ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
                        ->select([
                            'event_sessions.*',
                            'events.link as event_link',
                            'streams.start_at as start_at'
                        ]);
                }
            ])
            ->get(['event_sessions.*', 'events.link as event_link', 'streams.start_at as start_at']);
    }

    public function allChildByEventSessionId($eventSessionId)
    {
        $cacheKey = CacheKeys::fareByEventSessionIdKey($eventSessionId);

        $childEventSessions = CacheServiceFacade::get($cacheKey);

        if (!$childEventSessions) {
            $childEventSessions = $this->eventSession
                ->where('event_session_id', '=', $eventSessionId)
                ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
                ->join('events', 'events.id', 'event_sessions.event_id')
                ->get([
                    'event_sessions.*',
                    'streams.user_connected_count as stream_user_connected_count'
                ]);

            if (!empty($childEventSessions?->toArray())) {
                CacheServiceFacade::tags([
                    CacheKeys::eventSessionIdTag($eventSessionId),
                    ...CacheKeys::setEventSessionIdTags($childEventSessions->pluck['id'])
                ])
                    ->set($cacheKey, $childEventSessions, config('cache.ttl'));
            }
        }

        return $childEventSessions;
    }

    public function findByIdForAuthedUser($id): Model
    {
        return $this->eventSession
            ->currentAuthedUserByAuthId()
            ->with('children')
            ->findOrFail($id, [
                'event_sessions.*',
                'events.link as event_link',
                'projects.link as project_link'
            ]);
    }

    public function hasEventSessionByProjectLink($id, $projectLink)
    {
        $cacheKey = CacheKeys::hasEventSessionByProjectLink($id, $projectLink);

        $eventSession = CacheServiceFacade::get($cacheKey);

        if (!$eventSession) {
            $eventSession = $this->eventSession
                ->join('events', 'events.id', '=', 'event_sessions.event_id')
                ->join('projects', function ($join) use ($projectLink) {
                    $join
                        ->on('projects.id', '=', 'events.project_id')
                        ->where('projects.link', '=', $projectLink);
                })
                ->where('event_sessions.id', '=', $id)
                ->firstOrFail([
                    'event_sessions.event_id',
                    'projects.id as project_id'
                ]);

            CacheServiceFacade::tags([
                CacheKeys::projectIdTag($eventSession['project_id']),
                CacheKeys::eventIdTag($eventSession['event_id']),
                CacheKeys::eventSessionIdTag($id)
            ])
                ->set($cacheKey, $eventSession, config('cache.ttl'));
        }



        return (bool)$eventSession;
    }

    public function hasEventSessionByCodeAndEventLinkAndProjectLink($code, $eventLink, $projectLink)
    {

        $cacheKey = CacheKeys::hasEventSessionByCodeAndEventLinkAndProjectLink($code, $eventLink, $projectLink);
        $eventSession = CacheServiceFacade::get($cacheKey);
        if (!$eventSession) {
            $eventSession = $this->eventSession
                ->join('events', function ($join) use ($eventLink) {
                    $join
                        ->on('events.id', '=', 'event_sessions.event_id')
                        ->where('events.link', '=', $eventLink);
                })
                ->join('projects', function ($join) use ($projectLink) {
                    $join
                        ->on('projects.id', '=', 'events.project_id')
                        ->where('projects.link', '=', $projectLink);
                })
                ->where('event_sessions.code', '=', $code)
                ->firstOrFail();

            CacheServiceFacade::tags([
                CacheKeys::eventIdTag($eventSession['event_id']),
                CacheKeys::eventSessionIdTag($eventSession['id'])
            ])
                ->set($cacheKey, $eventSession, config('ttl'));
        }



        return (bool)$eventSession;
    }

    public function findByIdWithStream($id)
    {
        $cacheKey = CacheKeys::eventSessionByIdForRoomKey($id);
        $eventSession = CacheServiceFacade::get($cacheKey);

        if (!$eventSession) {
            $eventSession = $this->eventSession
                ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
                ->join('events', 'events.id', '=', 'event_sessions.event_id')
                ->join('projects', 'projects.id', '=', 'events.project_id')
                ->where('event_sessions.id', $id)
                ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE)
                ->get([
                    'event_sessions.*',
                    'projects.id as project_id',
                    'projects.link as project_link',
                    'events.cover_img_path as event_cover_img_path',
                    'events.is_unique_ticket_enabled as is_unique_ticket_enabled',
                    'streams.id as stream_id',
                    'streams.title as stream_title',
                    'streams.cover_img_path as stream_cover_img_path',
                    'streams.user_id as stream_user_id',
                    'streams.start_at as stream_start_at',
                    'streams.last_auth_at as stream_last_auth_at',
                    'streams.user_connected_count as stream_user_connected_count',
                    'streams.stream_status_id as stream_stream_status_id',
                    'streams.input as stream_input',
                    'streams.is_onair as stream_is_onair',
                    'streams.onair_at as stream_onair_at',
                    'streams.is_dvr_enabled as stream_is_dvr_enabled',
                    'streams.is_dvr_out_enabled as stream_is_dvr_out_enabled',
                    'streams.is_fullhd_enabled as stream_is_fullhd_enabled',
                    'streams.key as stream_key',
                    'streams.output as stream_output',
                    'streams.created_at as stream_created_at',
                    'streams.updated_at as stream_updated_at',
                ]);

            if ($eventSession->isNotEmpty()) {
                CacheServiceFacade::tags([
                    CacheKeys::projectIdTag($eventSession[0]['project_id']),
                    CacheKeys::eventIdTag($eventSession[0]['event_id']),
                    CacheKeys::eventSessionIdTag($eventSession[0]['id']),
                    CacheKeys::streamIdTag($eventSession[0]['stream_id'])
                ])
                    ->set($cacheKey, $eventSession, config('cache.ttl'));
            }
        }

        return $eventSession;
    }

    public function findByEventLinkAndSessionCodeWithStream($eventLink, $code)
    {
        $cacheKey = CacheKeys::eventSessionByCodeAndEventLink($code, $eventLink);
        $eventSession = CacheServiceFacade::get($cacheKey);
        if (!$eventSession) {
            $eventSession = $this->eventSession
                ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
                ->join('events', function ($join) use ($eventLink) {
                    $join
                        ->on('events.id', '=', 'event_sessions.event_id')
                        ->where('events.link', '=', $eventLink);
                })
                ->join('projects', 'projects.id', '=', 'events.project_id')
                ->where('event_sessions.code', $code)
                ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE)
                ->get([
                    'event_sessions.*',
                    'projects.id as project_id',
                    'projects.link as project_link',
                    'events.cover_img_path as event_cover_img_path',
                    'events.is_unique_ticket_enabled as is_unique_ticket_enabled',
                    'streams.id as stream_id',
                    'streams.title as stream_title',
                    'streams.cover_img_path as stream_cover_img_path',
                    'streams.user_id as stream_user_id',
                    'streams.start_at as stream_start_at',
                    'streams.last_auth_at as stream_last_auth_at',
                    'streams.user_connected_count as stream_user_connected_count',
                    'streams.stream_status_id as stream_stream_status_id',
                    'streams.input as stream_input',
                    'streams.is_onair as stream_is_onair',
                    'streams.onair_at as stream_onair_at',
                    'streams.is_dvr_enabled as stream_is_dvr_enabled',
                    'streams.is_dvr_out_enabled as stream_is_dvr_out_enabled',
                    'streams.is_fullhd_enabled as stream_is_fullhd_enabled',
                    'streams.key as stream_key',
                    'streams.output as stream_output',
                    'streams.created_at as stream_created_at',
                    'streams.updated_at as stream_updated_at',
                ]);

            CacheServiceFacade::tags([
                CacheKeys::projectIdTag($eventSession[0]['project_id']),
                CacheKeys::eventIdTag($eventSession[0]['event_id']),
                CacheKeys::eventSessionIdTag($eventSession[0]['id']),
                CacheKeys::streamIdTag($eventSession[0]['stream_id'])
            ])
                ->set($cacheKey, $eventSession, config('cache.ttl'));
        }



        return $eventSession;
    }

    public function findByIdOnlyEventSession($id): Model
    {
        return $this->eventSession
            ->currentAuthedUserByAuthId()
            ->findOrFail($id, [
                'event_sessions.*'
            ]);
    }

    public function getCurrentUserRolesByEventSession($eventSessionId)
    {
        return $this->eventSession
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', 'role_user.access_group_id', '=', 'access_groups.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users as users', function ($join) {
                $join
                    ->on('users.id', '=', 'role_user.user_id')
                    ->where('users.id', Auth::id());
            })
            ->where('event_sessions.id', $eventSessionId)
            ->get([
                'roles.*',
            ]);
    }

    public function isCurrentAuthedUserAllowed($eventSessionId)
    {
        return $this->eventSession
            ->currentAuthedUserIsAllowed($eventSessionId)
            ->exists();
    }

    public function getAccessGroupId($eventSessionId)
    {
        return $this->eventSession
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->where('event_sessions.id', '=', $eventSessionId)
            ->firstOrFail([
                'events.access_group_id'
            ])
            ?->access_group_id;
    }

    public function accessGroupIdByEventSessionId($eventSessionId)
    {
        $cacheKey = CacheKeys::accessGroupByEventSessionIdKey($eventSessionId);
        $accessGroupId = CacheServiceFacade::get($cacheKey);

        if (!$accessGroupId) {
            $accessGroupId = $this->eventSession
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->where('event_sessions.id', '=', $eventSessionId)
                ->first(['events.access_group_id'])
                ?->access_group_id;

            if ($accessGroupId) {
                CacheServiceFacade::tags([
                    CacheKeys::eventSessionIdTag($eventSessionId),
                ])
                    ->set($cacheKey,  $accessGroupId, config('cache.ttl'));
            }
        }
        return $accessGroupId;
    }

    public function getStreamByEventSessionId($eventSessionId)
    {
        return $this->eventSession
            ->join('streams',  'streams.id', '=', 'event_sessions.stream_id')
            ->where('event_sessions.id', '=', $eventSessionId)
            ->firstOrFail([
                'streams.*'
            ]);
    }


    public function allForAdmin(){
        $perPage = (int)Request::get('perPage', 50);
        $page = (int)Request::get('page', 1);
        $search = Request::get('search', false);

        $query = $this->eventSession
            ->select('event_sessions.*',
                    'streams.start_at','streams.onair_at','streams.is_onair','streams.key','streams.user_connected_count',
                    'streams.cover_img_path as stream_cover_img_path',
                    'events.name as event_name','events.cover_img_path as event_cover_img_path',
                    'projects.id as project_id', 'projects.name as project_name',
                    'fares.name as fare_name',
                    'users.id as user_id', 'users.name as user_name', 'users.lastname as user_lastname'
                )
            ->join('events', 'events.id', 'event_sessions.event_id')
            ->join('projects', 'projects.id', 'events.project_id')
            ->join('access_groups', 'access_groups.id', 'events.access_group_id')
            ->join('role_user', function ($join) {
                $join
                    ->on('role_user.access_group_id', 'access_groups.id')
                    ->where('role_id', Roles::ADMIN);
            })
            ->join('users', 'users.id', 'role_user.user_id')
            ->join('streams', 'streams.id', 'event_sessions.stream_id')
            ->join('fares', 'fares.id', 'event_sessions.fare_id');

        if($search){
            $query = $query->where(function ($query) use ($search) {
                $query->where('streams.key', $search)
                    ->orWhere('event_sessions.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('events.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('projects.name', 'LIKE', '%'.$search.'%');
            });
        }

        return $query
            ->orderBy('streams.start_at', 'desc')
            ->paginate(perPage: $perPage, page: $page);
    }

    public function findByIdWithEvent($id)
    {

        $eventSession = $this->eventSession
            ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
            ->join('events', 'events.id', '=', 'event_sessions.event_id')
            ->join('projects', 'projects.id', '=', 'events.project_id')
            ->where('event_sessions.id', $id)
            ->first([
                'event_sessions.*',
                'projects.id as project_id',
                'projects.link as project_link',
                'events.link as event_link',
                'events.name as event_name',
                'events.cover_img_path as event_cover_img_path',
                'events.is_unique_ticket_enabled as is_unique_ticket_enabled',
                'streams.id as stream_id',
                'streams.title as stream_title',
                'streams.cover_img_path as stream_cover_img_path',
                'streams.user_id as stream_user_id',
                'streams.start_at as stream_start_at',
                'streams.last_auth_at as stream_last_auth_at',
                'streams.user_connected_count as stream_user_connected_count',
                'streams.stream_status_id as stream_stream_status_id',
                'streams.input as stream_input',
                'streams.is_onair as stream_is_onair',
                'streams.onair_at as stream_onair_at',
                'streams.is_dvr_enabled as stream_is_dvr_enabled',
                'streams.is_dvr_out_enabled as stream_is_dvr_out_enabled',
                'streams.is_fullhd_enabled as stream_is_fullhd_enabled',
                'streams.key as stream_key',
                'streams.output as stream_output',
                'streams.created_at as stream_created_at',
                'streams.updated_at as stream_updated_at',
            ]);



        return $eventSession;
    }


    public function findAllByEventId($eventId)
    {
        return  $this->eventSession
            ->select(
                'event_sessions.id',
                'event_sessions.name',
                'event_sessions.event_session_status_id',
                'event_sessions.code',
                'event_sessions.key',
                'event_sessions.channel',
                'event_sessions.private_channel',
                'streams.cover_img_path',
                'streams.start_at',
                'streams.is_onair',
                'streams.user_connected_count',
                'fares.name as fare_name')
            ->join('fares', 'event_sessions.fare_id', 'fares.id')
            ->join('streams', 'event_sessions.stream_id', 'streams.id')
            ->where('event_id', $eventId)
            ->get();
    }
}
