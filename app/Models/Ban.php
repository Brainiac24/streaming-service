<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;

class Ban extends BaseModel
{

    public $fillable = [
        'user_id',
        'event_id',
        'chat_id',
        'created_by',
    ];

    public function scopeCurrentAuthedUser(Builder $query, $eventId)
    {
        return $query
            ->join('events',  function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'bans.event_id')
                    ->where('events.id', $eventId);
            })
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
