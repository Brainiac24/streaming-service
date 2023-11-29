<?php

namespace App\Exceptions;

use Exception;

class NimbleNotFoundException extends Exception
{
    public function render()
    {
        return response('error', 404);
    }
}
