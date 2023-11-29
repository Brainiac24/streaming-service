<?php

namespace App\Exceptions\Auth;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailIsAlreadyVerifiedException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::WRONG_CREDENTIALS_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Wrong credentials error: Email is already verified!')
            ),
            401
        );
    }
}
