<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseJsonResource;

class UserBasicInfoResource extends BaseJsonResource
{
    public function __construct($user)
    {
        $this->data = [
            'id' => $user->id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'is_verified' => $user->is_verified,
            'lang' => $user->lang,
            'avatar_path' => $user->avatar_path,
            'is_guest' => $user->wasRecentlyCreated,
        ];
    }
}
