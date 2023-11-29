<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\BaseJsonResource;

class EventItemSupportResource extends BaseJsonResource
{
    public function __construct($project)
    {
        $this->data = [
            'name' => $project['support_name'],
            'link' => $project['support_link'],
            'phone' => $project['support_phone'],
            'email' => $project['support_email'],
            'site' => $project['support_site'],
        ];
    }
}
