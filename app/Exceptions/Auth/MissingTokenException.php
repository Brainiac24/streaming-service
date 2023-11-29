<?php

namespace App\Exceptions\Auth;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class MissingTokenException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::WRONG_CREDENTIALS_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Wrong credentials error: Token is missing!')
            ),
            401
        );
    }
}
