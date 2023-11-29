<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventDataCollectionTemplate extends BaseModel
{
    public $fillable = [
        'event_id',
        'name',
        'label',
        'is_required',
    ];

    public function setNameAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['name'] = NULL;
        } else {
            $this->attributes['name'] = $value;
        }
    }

    public function scopeCurrentAuthedUserByAuthedId(Builder $query)
    {
        return $query
            ->join('events',  'events.id', '=', 'event_data_collection_templates.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function scopeCurrentAuthedUserByEventId(Builder $query, $eventId)
    {
        return $query
            ->join('events',  function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'event_data_collection_templates.event_id')
                    ->where('events.id', $eventId);
            })
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
