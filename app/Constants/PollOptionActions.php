<?php

namespace App\Constants;

class PollOptionActions
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';

    const ACTIONS = [
        self::CREATE,
        self::UPDATE,
        self::DELETE,
    ];
}
