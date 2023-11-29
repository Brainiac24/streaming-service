<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use App\Exceptions\BaseException;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class ValidationException extends BaseException
{
    function __construct($message = "", $code = 0, $previous = null, public $data = [])
    {
        parent::__construct($message, $code, $previous);
    }
    public function render(): JsonResponse
    {
        return Response::apiError(
            new BaseJsonResource(
                code: StatusCodes::VALIDATION_ERROR,
                message: !empty($this->message) ? __($this->message) : __('Validation error!'),
                data: $this->data
            ),
            422
        );
    }
}
