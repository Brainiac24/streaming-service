<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class WrongCredentialException extends BaseException
{

    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::WRONG_CREDENTIALS_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Wrong credentials error!')
            ),
            422
        );
    }
}
