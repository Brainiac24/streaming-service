<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
class ContactGroup extends BaseModel
{
    public $fillable = [
        'name',
        'event_id',
        'is_common',
        'is_active',
    ];

    public function scopeCurrentAuthedUserByAuthedId(Builder $query)
    {
        return $query
            ->join('events',  'events.id', '=', 'contact_groups.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }
}
