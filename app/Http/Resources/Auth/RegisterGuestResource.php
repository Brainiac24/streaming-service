<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\BaseJsonResource;

class RegisterGuestResource extends BaseJsonResource
{
    public function __construct($type, $sessionKey, $userToken)
    {
        $this->data = [
            'token' => $userToken,
            'url' => env("APP_URL") . "/widget/" . $type . "/" . $sessionKey . "/" . $userToken
        ];
    }
}
