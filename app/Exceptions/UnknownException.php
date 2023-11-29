<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Http\JsonResponse;
use Response;

class UnknownException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::UNKNOWN_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Unknown error!')
            )
        );
    }
}
