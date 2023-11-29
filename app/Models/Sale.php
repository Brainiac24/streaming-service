<?php

namespace App\Models;

class Sale extends BaseModel
{
    public $fillable = [
        'event_session_id',
        'title',
        'description',
        'button_text',
        'url',
        'cover_img_path',
        'sale_status_id',
        'clicks_count',
        'sort',
    ];
}
