<?php

namespace App\Repositories\Event;

use App\Constants\CacheKeys;
use App\Constants\EventSessionStatuses;
use App\Constants\Roles;
use App\Models\Event;
use App\Models\RoleUser;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class EventRepository extends BaseRepository
{
    public function __construct(
        public Event $event,
        public RoleUser $roleUser
    ) {
        parent::__construct($event);
    }

    public function findById($id)
    {
        $event = CacheServiceFacade::get(CacheKeys::eventKey($id));

        if (!$event) {
            $event = parent::findById($id);

            CacheServiceFacade::tags([CacheKeys::eventIdTag($id)])
                ->set(CacheKeys::eventKey($id), $event, config('cache.ttl'));
        }

        return $event;
    }

    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->event
            ->currentAuthedUser()
            ->findOrFail($id, [
                'events.*',
                'projects.link as project_link'
            ]);
    }
    public function findByLink($link)
    {
        return $this->event
            ->where('events.link', $link)
            ->first([
                'events.*'
            ]);
    }

    public function findByProjectIdForCurrentAuthedUser($projectId)
    {
        return $this->event
            ->currentAuthedUser()
            ->leftJoin('event_sessions', function ($join) {
                $join
                    ->on('event_sessions.event_id', '=', 'events.id')
                    ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE);
            })
            ->leftJoin('streams', function ($join) {
                $join
                    ->on('streams.id', '=', 'event_sessions.stream_id')
                    ->where('streams.start_at', '>', now()->format("Y-m-d H:i:s"));
            })
            ->where('events.project_id', $projectId)
            ->orderBy('streams.start_at', 'asc')
            ->orderBy('events.id', 'desc')
            ->groupBy('events.id')
            ->get(['events.*', 'streams.start_at as stream_start_at']);
    }

    public function updateByModelForCurrentAuthedUser(array $data, $event)
    {
        $event->update($data);
        CacheServiceFacade::forget(CacheKeys::eventKey($event['id']));
        CacheServiceFacade::forget(CacheKeys::receptionByEventIdKey($event['id']));
        return $event;
    }

    public function update(array $data, $id)
    {
        $model = parent::update($data, $id);
        CacheServiceFacade::forget(CacheKeys::eventKey($id));
        CacheServiceFacade::forget(CacheKeys::receptionByEventIdKey($id));
        return $model;
    }

    public function findSessionsCountById($id)
    {

        $cacheKey = CacheKeys::eventSessionCountByEventIdKey($id);
        $eventCount = CacheServiceFacade::get($cacheKey);
        if (!$eventCount) {
            $eventCount = count($this->event
                ->join('event_sessions', function ($join) {
                    $join
                        ->on('event_sessions.event_id', '=', 'events.id')
                        ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE);
                })
                ->where('events.id', $id)
                ->get(['event_sessions.id'])->toArray());
            CacheServiceFacade::tags([
                CacheKeys::eventIdTag($id)
            ])
                ->set($cacheKey, $eventCount, config('cache.ttl'));
        }


        return $eventCount;
    }

    public function getUsersWithRolesForCurrentAuthedUser($eventId, $roleName = null)
    {
        return $this->event
            ->currentAuthedUser()
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', 'role_user.access_group_id', '=', 'access_groups.id')
            ->join('roles', function ($join) use ($roleName) {
                $join
                    ->on('roles.id', '=', 'role_user.role_id')
                    ->when($roleName, function ($query) use ($roleName) {
                        $roleName = strtoupper($roleName);
                        $query->where('roles.id', '=', constant(Roles::class . '::' . strtoupper($roleName)));
                    });
            })
            ->join('users as event_users', 'event_users.id', '=', 'role_user.user_id')
            ->where('events.id', $eventId)

            ->get([
                'event_users.id',
                'roles.display_name as role_label',
                'event_users.name',
                'event_users.lastname',
                'event_users.email',
                'event_users.email_verified_at',
                'event_users.is_verified',
                'event_users.lang',
                'event_users.avatar_path',
            ]);
    }

    public function getCurrentUserRoles($eventId)
    {
        return $this->event
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', 'role_user.access_group_id', '=', 'access_groups.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users as event_users', function ($join) {
                $join
                    ->on('event_users.id', '=', 'role_user.user_id')
                    ->where('event_users.id', Auth::id());
            })
            ->where('events.id', $eventId)
            ->get([
                'roles.*',
            ]);
    }

    public function findByEventSessionId($eventSessionId)
    {
        $cacheKey = CacheKeys::eventByEventSessionIdKey($eventSessionId);

        $event = CacheServiceFacade::get($cacheKey);
        if (empty($event)) {
            $event = $this->event
                ->join('event_sessions', function ($join) use ($eventSessionId) {
                    $join
                        ->on('event_sessions.event_id', '=', 'events.id')
                        ->where('event_sessions.id', $eventSessionId);
                })
                ->first([
                    'events.*'
                ]);

            if (!empty($event)) {
                CacheServiceFacade::tags([
                    CacheKeys::eventIdTag($event['id']),
                    CacheKeys::eventSessionIdTag($eventSessionId),
                ])
                    ->set($cacheKey, $event, config('cache.ttl'));
            }
        }

        return $event;
    }

    public function upcomingNotEnded()
    {
        return $this->event
            ->join('projects', 'projects.id', '=', 'events.project_id')
            ->join('event_sessions', function ($join) {
                $join
                    ->on('event_sessions.event_id', '=', 'events.id')
                    ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE);
            })
            ->join('streams',  function ($join) {
                $join
                    ->on('streams.id', '=', 'event_sessions.stream_id')
                    ->where(function ($query) {
                        $query
                            ->where('streams.start_at', '>', now()->format('Y-m-d H:i:s'))
                            ->orWhere('streams.is_onair', 1);
                    });
            })
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', 'role_user.access_group_id', '=', 'access_groups.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users as event_users', 'event_users.id', '=', 'role_user.user_id')
            ->where('event_users.id', Auth::id())
            ->orderBy('streams.start_at')
            ->get([
                'events.id',
                'events.name',
                'events.link',
                'events.cover_img_path',
                'event_sessions.id as event_session_id',
                'event_sessions.name as event_session_name',
                'event_sessions.code as event_session_code',
                'streams.id as stream_id',
                'streams.cover_img_path as stream_cover_img_path',
                'streams.start_at as stream_start_at',
                'streams.onair_at as stream_onair_at',
                'streams.is_onair as stream_is_onair',
                'roles.id as role_id',
                'roles.name as role_name',
                'roles.display_name as role_display_name',
                'projects.link as project_link'
            ]);
    }

    public function upcomingEnded()
    {
        return $this->event
            ->join('projects', 'projects.id', '=', 'events.project_id')
            ->join('event_sessions', function ($join) {
                $join
                    ->on('event_sessions.event_id', '=', 'events.id')
                    ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE);
            })
            ->join('streams',  function ($join) {
                $join
                    ->on('streams.id', '=', 'event_sessions.stream_id')
                    ->where('streams.start_at', '<', now()->format('Y-m-d H:i:s'))
                    ->whereNotNull('onair_at')
                    ->where(function ($query) {
                        $query
                            ->whereNull('is_onair')
                            ->orWhere('is_onair', '=', false);
                    });
            })
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', 'role_user.access_group_id', '=', 'access_groups.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users as event_users', 'event_users.id', '=', 'role_user.user_id')
            ->where('event_users.id', Auth::id())
            ->orderBy('streams.start_at', 'desc')
            ->orderBy('events.id', 'desc')
            ->get([
                'events.id',
                'events.name',
                'events.link',
                'events.cover_img_path',
                'event_sessions.id as event_session_id',
                'event_sessions.name as event_session_name',
                'event_sessions.code as event_session_code',
                'streams.id as stream_id',
                'streams.cover_img_path as stream_cover_img_path',
                'streams.start_at as stream_start_at',
                'streams.onair_at as stream_onair_at',
                'streams.is_onair as stream_is_onair',
                'roles.id as role_id',
                'roles.name as role_name',
                'roles.display_name as role_display_name',
                'projects.link as project_link'
            ]);
    }

    public function findByIdForReception($id)
    {
        return $this->event
            ->join('projects', 'projects.id', '=', 'events.project_id')
            ->join('event_sessions', function ($join) {
                $join
                    ->on('event_sessions.event_id', '=', 'events.id')
                    ->where('event_sessions.event_session_status_id', EventSessionStatuses::ACTIVE);
            })
            ->join('streams', 'streams.id', '=', 'event_sessions.stream_id')
            ->where('events.id', $id)
            ->getOrFail([
                'events.*',
                'projects.id as project_id',
                'projects.support_name as projects_support_name',
                'projects.support_link as projects_support_link',
                'projects.support_phone as projects_support_phone',
                'projects.support_email as projects_support_email',
                'projects.support_site as projects_support_site',
                'projects.link as project_link',
                'event_sessions.id as event_session_id',
                'event_sessions.code as event_session_code',
                'event_sessions.name as event_session_name',
                'event_sessions.channel as event_session_channel',
                'event_sessions.private_channel as event_session_private_channel',
                'event_sessions.fare_id as event_session_fare_id',
                'event_sessions.logo_img_path as event_session_logo_img_path',
                'streams.id as stream_id',
                'streams.title as stream_title',
                'streams.cover_img_path as stream_cover_img_path',
                'streams.start_at as stream_start_at',
                'streams.onair_at as stream_onair_at',
                'streams.is_onair as stream_is_onair',
            ]);
    }


    public function hasEventByLinkAndProjectLink($link, $projectLink)
    {
        $hasEvent = CacheServiceFacade::get(CacheKeys::hasEventByLinkAndProjectLinkKey($link, $projectLink));
        if (!$hasEvent) {
            $event = $this->event
                ->join('projects', function ($join) use ($projectLink) {
                    $join
                        ->on('projects.id', '=', 'events.project_id')
                        ->where('projects.link', '=', $projectLink);
                })
                ->where('events.link', '=', $link)
                ->firstOrFail([
                    'events.id',
                    'projects.id as project_id',
                ]);

            $hasEvent = (bool)$event['id'];

            CacheServiceFacade::tags([
                CacheKeys::projectIdTag($event['project_id']),
                CacheKeys::eventIdTag($event['id'])
            ])
                ->set(
                    CacheKeys::hasEventByLinkAndProjectLinkKey($link, $projectLink),
                    $hasEvent,
                    config('cache.ttl')
                );
        }
        return $hasEvent;
    }

    public function projectIdById($eventId)
    {
        $projectId = CacheServiceFacade::get(CacheKeys::projectIdByEventIdKey($eventId));

        if (!$projectId) {
            $project = $this->event
                ->join('projects', 'projects.id', '=', 'events.project_id')
                ->where('events.id', '=', $eventId)
                ->firstOrFail([
                    'events.id',
                    'projects.id as project_id',
                ]);

            $projectId = $project['project_id'];

            CacheServiceFacade::tags([
                CacheKeys::projectIdTag($project['project_id']),
                CacheKeys::eventIdTag($project['id'])
            ])
                ->set(
                    CacheKeys::projectIdByEventIdKey($eventId),
                    $projectId,
                    config('cache.ttl')
                );
        }

        return $projectId;
    }
    public function eventIdByLink($link)
    {
        $eventId = CacheServiceFacade::get(CacheKeys::eventIdByEventLinkKey($link));
        if (!$eventId) {
            $event = $this->event
                ->where('events.link', '=', $link)
                ->firstOrFail('events.id');

            CacheServiceFacade::tags(CacheKeys::eventIdTag($event['id']))
                ->set(
                    CacheKeys::eventIdByEventLinkKey($link),
                    $event['id'],
                    config('cache.ttl')
                );

            $eventId = $event['id'];
        }

        return $eventId;
    }

    public function allByUserId($user_id)
    {
        $perPage = (int)Request::get('perPage', 20);
        $page = (int)Request::get('page', 1);
        $events = $this->event
            ->selectRaw(
                DB::getTablePrefix() . 'events.*, ' . DB::getTablePrefix() . 'roles.name as role_name,
                    count(' . DB::getTablePrefix() . 'roles.id) as roles_count,
                    (select count(id) from ' . DB::getTablePrefix() . 'event_sessions where event_id = events.id) as event_sessions_count'
            )
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', 'role_user.access_group_id', '=', 'access_groups.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users as event_users', 'event_users.id', '=', 'role_user.user_id')
            ->where('event_users.id', $user_id)
            ->groupBy('events.id')
            ->orderBy('events.start_at', 'desc')
            ->paginate(perPage: $perPage, page: $page);

        foreach ($events as &$event) {
            if ($event["roles_count"] > 1) {
                $event["roles"] = $this->roleUser
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where([
                        "user_id" => $user_id,
                        "access_group_id" => $event["access_group_id"]
                    ])
                    ->pluck('roles.name');
            } else {
                $event["roles"] = [$event["role_name"]];
            }
        }

        return $events;
    }

    public function allForAdminEvents(array $filter)
    {
        $perPage = (int)Request::get('perPage', 20);
        $page = (int)Request::get('page', 1);
        $search = Request::get('search', false);
        $dateFrom = Request::get('date_from', false);
        $dateTo = Request::get('date_to', false);

        $events = $this->event
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', 'role_user.access_group_id', '=', 'access_groups.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->join('projects', 'projects.id', '=', 'events.project_id')
            ->selectRaw(
                DB::getTablePrefix() . 'events.*,
                ' . DB::getTablePrefix() . 'users.id as user_id,
                ' . DB::getTablePrefix() . 'users.name as user_name,
                ' . DB::getTablePrefix() . 'users.lastname as user_lastname,
                ' . DB::getTablePrefix() . 'projects.name as project_name,
                ' . DB::getTablePrefix() . 'projects.link as project_link,
                (select count(id) from ' . DB::getTablePrefix() . 'event_sessions where event_id = ' . DB::getTablePrefix() . 'events.id) as event_sessions_count'
            );

        if ($search) {
            $events = $events->where(function ($query) use ($search) {
                $query->where('events.id', $search)
                    ->orWhere('events.name', 'LIKE', $search . '%')
                    ->orWhere('users.email', 'LIKE', $search . '%')
                    ->orWhere('users.name', 'LIKE', $search . '%')
                    ->orWhere('users.lastname', 'LIKE', $search . '%');
            });
        }

        // Показывает все записи до этой даты
        if ($dateFrom) {
            $dateTimeFrom = new DateTime();
            $dateTimeFrom->setTimestamp($filter['date_from']);
            $events->where('events.start_at', '>=', $dateTimeFrom->format('Y-m-d 00:00:00'));
        }

        // Показывает все записи после этой даты
        if ($dateTo) {
            $dateTimeTo = new DateTime();
            $dateTimeTo->setTimestamp($filter['date_to']);
            $events->where('events.end_at', '<=', $dateTimeTo->format('Y-m-d 23:59:59'));
        }

        return $events->groupBy('events.id')
            ->orderBy('events.start_at', 'desc')
            ->paginate(perPage: $perPage, page: $page);
    }
    public function findUserByEventId($eventId)
    {
        return $this->event
            ->join('projects',  'projects.id', '=', 'events.project_id')
            ->join('users',  'users.id', '=', 'projects.user_id')
            ->where('events.id', '=', $eventId)
            ->first(['users.*']);
    }

    public function getUserMembersByEventId($eventId)
    {
        return $this->event
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', function ($join) {
                $join
                    ->on('role_user.access_group_id', '=', 'access_groups.id')
                    ->where('role_user.role_id', '=', Roles::MEMBER);
            })
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->where('events.id', $eventId)
            ->get([
                'users.id',
                'users.name',
                'users.lastname',
                'users.email',
            ]);
    }

    public function findByIdForCurrentAuthedUserAndRequisite(int $eventId)
    {
        return $this->event
            ->currentAuthedUser()
            ->leftJoin('payment_requisites', 'payment_requisites.project_id', 'events.project_id')
            ->findOrFail($eventId, [
                'events.*',
                'projects.link as project_link',
                'payment_requisites.status as payment_requisites_status'
            ]);
    }

    public function getCurrentAuthedUserByIsTicketSalesEnabled(int $eventId)
    {
        return $this->event
            ->currentAuthedUser()
            ->where('is_ticket_sales_enabled', true)
            ->findOrFail($eventId, [
                'events.*',
                'projects.link as project_link'
            ]);
    }
}
