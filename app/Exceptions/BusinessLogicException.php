<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Http\JsonResponse;
use Response;

class BusinessLogicException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::BUSINESS_LOGIC_ERROR,
                message: !empty($this->message) ? __($this->message) :  __('Business logic error!')
            )
        );
    }
}
