<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseJsonResource;

class UserAdminListArrayResource extends BaseJsonResource
{
    public function __construct($users)
    {
        parent::__construct(data: $users);
        $this->data = [];

        foreach ($users as $item) {
            $fullname = trim($item['name'] . ' ' . $item['lastname']);
            $fullphone= trim(($item['phone_code'] ? '+'.$item['phone_code'].' ' : '') . $item['phone']);

            $this->data[] = [
                'id' => $item['id'],
                'created_at' => $item['created_at'],
                'email' => $item['email'],
                'phone' => $item['phone'],
                'fullphone' => $fullphone,
                'fullname' => $fullname,
                'email_verified_at' => $item['email_verified_at'],
                'is_verified' => (bool)$item['is_verified'],
                'avatar_path' => $item['avatar_path'],
                'is_guest' => !$item['email'],
                'project_count' => $item['project_count'],
                'event_count' => $item['event_count'],
                'balance' => round((float)$item['balance'], 2),
                'is_active' => $item['is_active'],
            ];
        }
    }
}
