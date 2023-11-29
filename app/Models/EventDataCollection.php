<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventDataCollection extends BaseModel
{

    public $fillable = [
        'event_id',
        'user_id',
        'event_data_collection_template_id',
        'value'
    ];

    public function scopeCurrentAuthedUserByEventId(Builder $query, $eventId)
    {
        return $query
            ->join('events',  function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'event_data_collections.event_id')
                    ->where('events.id', $eventId);
            })
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function scopeCurrentAuthedUserByAuthedId(Builder $query)
    {
        return $query
            ->join('events',  'events.id', '=', 'event_data_collections.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
