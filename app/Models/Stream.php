<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Stream extends BaseModel
{
    protected $table = 'streams';

    public $fillable = [
        'title',
        'event_id',
        'user_id',
        'start_at',
        'last_auth_at',
        'cover_img_path',
        'user_connected_count',
        'input',
        'output',
        'stream_status_id',
        'is_onair',
        'is_dvr_enabled',
        'is_dvr_out_enabled',
        'is_fullhd_enabled',
        'onair_at',
        'key',
    ];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
        'config_json' => 'array',
        'start_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeCurrentAuthedUser(Builder $query)
    {
        return $query
            ->join('event_sessions',  'event_sessions.stream_id',  'streams.id')
            ->join('events',  'events.id', 'event_sessions.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function scopeCurrentAuthedUserByEventSessionId(Builder $query, $eventSessionId)
    {
        return $query
            ->join('event_sessions',  function ($join) use ($eventSessionId) {
                $join
                    ->on('event_sessions.stream_id', '=', 'streams.id')
                    ->where('event_sessions.event_session_id', $eventSessionId);
            })
            ->join('events',  'events.id', 'event_sessions.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function scopeCurrentAuthedUserByEventSessionIdNotParent(Builder $query, $eventSessionId)
    {
        return $query
            ->join('event_sessions',  function ($join) use ($eventSessionId) {
                $join
                    ->on('event_sessions.stream_id', '=', 'streams.id')
                    ->where('event_sessions.id', $eventSessionId)
                    ->whereNull('event_sessions.event_session_id');
            })
            ->join('events',  'events.id', 'event_sessions.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
