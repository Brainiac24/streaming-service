<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use App\Http\Resources\BaseJsonResource;
use Exception;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseException extends Exception
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::UNKNOWN_ERROR,
                message: !empty($this->message) ? __($this->message) :  __('Error. Token is missing!')
            )
        );
    }
}
