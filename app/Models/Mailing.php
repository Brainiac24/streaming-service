<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Mailing extends BaseModel
{
    public $fillable = [
        'job_uuid',
        'data_json',
        'mailing_status_id',
        'mailing_requisite_id',
        'message_template_id',
        'message_title',
        'event_id',
        'event_session_id',
        'contact_group_id',
        'is_default',
        'delay_count',
        'send_at'
    ];

    protected $casts = [
        'data_json' => 'array',
        'send_at' => 'datetime'
    ];

    public function scopeCurrentAuthedUserByAuthedId(Builder $query)
    {
        return $query
            ->join('events',  'events.id', '=', 'mailings.event_id')
            ->join('projects', function ($join) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('projects.user_id', Auth::id());
            });
    }

}
