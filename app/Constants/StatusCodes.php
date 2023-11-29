<?php

namespace App\Constants;

class StatusCodes
{
    const SUCCESS = 0;
    const IN_PROCESS = 1;
    const UNKNOWN_ERROR = -1;
    const NOT_FOUND_ERROR = -2;
    const WRONG_CREDENTIALS_ERROR = -3;
    const VALIDATION_ERROR = -4;
    const AUTHENTICATION_ERROR = -5;

    const ACCESS_FORBIDDEN_ERROR = -6;
    const DUPLICATE_DATA_ERROR = -7;
    const BUSINESS_LOGIC_ERROR = -8;

    const TOKEN_EXPIRED_ERROR = -9;
    const TOKEN_NOT_VALID_ERROR = -10;
    const TOKEN_REFRESH_ERROR = -11;
}
