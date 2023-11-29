<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;

class EventTicket extends BaseModel
{

    public $fillable = [
        'event_id',
        'user_id',
        'ticket',
        'event_ticket_status_id',
        'event_ticket_type_id',
        'price'
    ];

    public function scopeCurrentAuthedUser(Builder $query, $eventId)
    {
        return $query
            ->join('events',  function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'event_tickets.event_id')
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
            ->join('events',  'events.id', '=', 'event_tickets.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
