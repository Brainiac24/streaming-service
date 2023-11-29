<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Project extends BaseModel
{

    public $fillable = [
        'name',
        'user_id',
        'link',
        'cover_img_path',
        'project_status_id',
        'support_name',
        'support_link',
        'support_phone',
        'support_email',
        'support_site',
    ];

    public function scopeCurrentAuthedUser(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }
}
