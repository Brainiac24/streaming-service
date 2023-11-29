<?php

namespace App\Exceptions;

use Exception;

class NimbleUnauthorizedException extends Exception
{
    public function render()
    {
        return response('error', 401);
    }
}
