<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;

class MailingRequisite extends BaseModel
{
    public $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'token',
        'data_json',
        'is_active',
        'project_id',
    ];

    protected $casts = [
        'data_json' => 'array'
    ];

    public function scopeCurrentAuthedUser(Builder $query): void
    {
        $query->join('projects', function ($join) {
            $join
                ->on('projects.id', '=', 'mailing_requisites.project_id')
                ->where('projects.user_id', Auth::id());
        });
    }
}
