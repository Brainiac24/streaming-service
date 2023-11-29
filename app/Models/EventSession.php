<?php

namespace App\Models;

use App\Constants\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventSession extends BaseModel
{
    public $fillable = [
        'event_id',
        'name',
        'content',
        'config_json',
        'event_session_status_id',
        'stream_id',
        'event_session_id',
        'sort',
        'fare_id',
        'code',
        'logo_img_path',
        'key',
        'channel',
        'private_channel',
    ];

    protected $casts = [
        'config_json' => 'array'
    ];

    public function parent()
    {
        return $this->belongsTo(EventSession::class);
    }

    public function children()
    {
        return $this->hasMany(EventSession::class);
    }

    public function scopeCurrentAuthedUser(Builder $query, $eventId)
    {
        return $query
            ->join('events',  function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'event_sessions.event_id')
                    ->where('events.id', $eventId);
            })
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function scopeCurrentAuthedUserByAuthId(Builder $query)
    {
        return $query
            ->join('events', 'events.id', '=', 'event_sessions.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }

    public function scopeCurrentAuthedUserIsAllowed(Builder $query, $eventSessionId): void
    {
        $query
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('projects', 'projects.id', '=', 'events.project_id')
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', function ($join) {
                $join
                    ->on('role_user.access_group_id', '=', 'access_groups.id')
                    ->where(function ($query) {
                        $query
                            ->orWhere('role_user.role_id', '=', Roles::ADMIN)
                            ->orWhere('role_user.role_id', '=', Roles::MODERATOR);
                    });
            })
            ->where('event_sessions.id', '=', $eventSessionId);
    }
}
