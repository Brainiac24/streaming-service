<?php

namespace App\Exceptions\Auth;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class RefreshTokenErrorException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::TOKEN_REFRESH_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Validation error: Refresh token is not valid!')
            ),
            401
        );
    }
}
