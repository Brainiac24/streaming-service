<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;

class Contact extends BaseModel
{
    public $fillable = [
        'email',
        'name',
        'lastname',
        'data_json',
        'user_id',
        'contact_group_id',
        'event_ticket_id',
        'event_ticket_type_id'
    ];

    protected $casts = [
        'data_json' => 'array'
    ];

    public function scopeCurrentAuthedUserByAuthedId(Builder $query)
    {
        return $query
            ->join('contact_groups',  'contact_groups.id', '=', 'contacts.contact_group_id')
            ->join('events',  'events.id', '=', 'contact_groups.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

    public function scopeCurrentAuthedUserByAuthedIdAndEventId(Builder $query,$eventId )
    {
        return $query
            ->join('contact_groups', 'contact_groups.id', 'contacts.contact_group_id')
            ->join('events', function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'contact_groups.event_id')
                    ->where('events.id', $eventId);
            })
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
