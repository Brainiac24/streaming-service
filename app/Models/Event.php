<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;

class Event extends BaseModel
{

    public $fillable = [
        'name',
        'project_id',
        'is_active',
        'cover_img_path',
        'logo_img_path',
        'description',
        'link',
        'start_at',
        'end_at',
        'config_json',
        'event_status_id',
        'access_group_id',
        'is_unique_ticket_enabled',
        'is_multi_ticket_enabled',
        'is_data_collection_enabled',
        'is_ticket_sales_enabled',
        'ticket_price',
    ];

    protected $casts = [
        'config_json' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_unique_ticket_enabled' => 'boolean',
        'is_multi_ticket_enabled' => 'boolean',
        'is_data_collection_enabled' => 'boolean',
    ];

    public function accessGroup()
    {
        return $this->belongsTo(AccessGroup::class);
    }

    public function scopeCurrentAuthedUser(Builder $query): void
    {
        $query->join('projects', function ($join) {
            $join
                ->on('projects.id', '=', 'events.project_id')
                ->where('projects.user_id', Auth::id());
        });
    }
}
