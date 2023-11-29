<?php

namespace App\Exceptions\User;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Http\JsonResponse;
use Response;

class CannotCreateUserException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::WRONG_CREDENTIALS_ERROR,
                message: !empty($this->message) ? __($this->message) : __("Wrong credentials error: Can't create user with provided credentials!")
            )
        );
    }
}
