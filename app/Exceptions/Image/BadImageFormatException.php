<?php

namespace App\Exceptions\Image;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BadImageFormatException extends BaseException
{
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::VALIDATION_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Validation error: Provided unsupported image format!')
            )
        );
    }
}
