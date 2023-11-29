<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventDataCollectionDictionary extends BaseModel
{
    use HasFactory;

    public $fillable = [
        'name',
        'is_required',
        'is_editable',
        'is_custom',
        'is_active',
    ];
}
