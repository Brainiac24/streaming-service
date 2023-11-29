<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventSessionVisit extends BaseModel
{


    public $fillable = [
        'event_session_id',
        'user_id',
        'ip',
        'url',
        'useragent',
        'source',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function scopeCurrentAuthedUserByEventSessionId(Builder $query, $eventSessionId)
    {
        return $query
            ->join('event_sessions',  function ($join) use ($eventSessionId) {
                $join
                    ->on('event_sessions.id', '=', 'event_session_visits.event_session_id')
                    ->where('event_session_visits.event_session_id', $eventSessionId);
            })
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function scopeCurrentAuthedUserByEventStreamId(Builder $query, $streamId)
    {
        return $query
            ->join('event_sessions', 'event_sessions.id', '=', 'event_session_visits.event_session_id')
            ->join('streams',  function ($join) use ($streamId) {
                $join
                    ->on('streams.id', '=', 'event_sessions.stream_id')
                    ->where('streams.id', $streamId);
            })
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
