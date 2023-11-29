<?php

namespace App\Http\Resources\Bans;

use App\Http\Resources\BaseJsonResource;

class BanListResource extends BaseJsonResource
{
    public function __construct($data)
    {
        parent::__construct(data: $data);
        $this->data = [];

        foreach ($data as $item) {
            $fullname = trim($item['user_name'] . ' ' . $item['user_lastname']);
            $fullnameCreatedUser = trim($item['created_user_name'] . ' ' . $item['created_user_lastname']);
            $this->data[] = [
                'id' => $item['id'],
                'event_id' => $item['event_id'],
                'user_id' => $item['user_id'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
                'user' => [
                    'fullname' => $fullname,
                    'email' => $item['user_email'],
                ],
                'created_by_user' => [
                    'fullname' => $fullnameCreatedUser,
                    'email' => $item['created_user_email'],
                ]
            ];
        }
    }
}
