<?php

namespace App\Exceptions\User;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BalanceNotEnoughException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::VALIDATION_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Validation error: Balance is not enough to proceed this operation!')
            )
        );
    }
}
