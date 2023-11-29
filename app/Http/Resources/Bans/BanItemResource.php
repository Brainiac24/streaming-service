<?php

namespace App\Http\Resources\Bans;

use App\Http\Resources\BaseJsonResource;

class BanItemResource extends BaseJsonResource
{
    public function __construct($data)
    {
        $fullname = trim($data['user_name'] . ' ' . $data['user_lastname']);
        $fullnameCreatedUser = trim($data['created_user_name'] . ' ' . $data['created_user_lastname']);
        $this->data = [
            'id' => $data['id'],
            'event_id' => $data['event_id'],
            'user_id' => $data['user_id'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'user' => [
                'fullname' => $fullname,
                'email' => $data['user_email'],
            ],
            'created_by_user' => [
                'fullname' => $fullnameCreatedUser,
                'email' => $data['created_user_email'],
            ]
        ];
    }
}
