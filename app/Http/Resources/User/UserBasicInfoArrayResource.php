<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseJsonResource;

class UserBasicInfoArrayResource extends BaseJsonResource
{
    public function __construct($data)
    {
        foreach ($data as $item) {
            $fullname = trim($item['name'] . ' ' . $item['lastname']);
            $this->data[] = [
                'id' => $item['id'],
                'role_label' => isset($item['role_label']) ? __($item['role_label']) : '',
                'fullname' => $fullname,
                'email' => $item['email'],
                'email_verified_at' => $item['email_verified_at'],
                'is_verified' => (bool)$item['is_verified'],
                'lang' => $item['lang'],
                'avatar_path' => $item['avatar_path'],
                'is_guest' => !(bool)$fullname,
            ];
        }
    }
}
