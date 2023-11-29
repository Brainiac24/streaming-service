<?php

namespace App\Http\Resources\Chat;

use App\Http\Resources\BaseJsonResource;
use Auth;

class ChatExportResource extends BaseJsonResource
{
    public function __construct($messages)
    {
        $headers = [
            'datetime' => __('DateTime'),
            'email' => __('Email'),
            'name' => __('Name'),
            'lastname' => __('Lastname'),
            'message' => __('Message'),
        ];
        $this->data[] = $headers;
        foreach ($messages as $item) {
            $this->data[] = [
                'datetime' => $item['datetime'],
                'email' => $item['email'],
                'name' => $item['name'],
                'lastname' => $item['lastname'],
                'message' => $item['message'],
            ];
        }
    }
}
