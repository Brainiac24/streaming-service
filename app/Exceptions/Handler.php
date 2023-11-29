<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use Firebase\JWT\ExpiredException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException as LaravelValidationException;

use Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        $result = '';
        if ($e instanceof ExpiredException) {
            return Response::apiError(['code' => StatusCodes::TOKEN_EXPIRED_ERROR, 'message' => $e->getMessage()], 401);
        } else if ($e instanceof AuthenticationException) {
            return Response::apiError(['code' => StatusCodes::AUTHENTICATION_ERROR, 'message' => $e->getMessage()], 401);
        } else if ($e instanceof ValidationException) {
            return Response::apiError(['code' => StatusCodes::VALIDATION_ERROR, 'message' => __($e->getMessage())], 422);
        } else if ($e instanceof LaravelValidationException) {
            return Response::apiError(['code' => StatusCodes::VALIDATION_ERROR, 'message' => __($e->getMessage())], 422);
        } else if ($e instanceof ModelNotFoundException) {
            return Response::apiError(['code' => StatusCodes::NOT_FOUND_ERROR, 'message' => __('Not Found!')], 404);
        } else if ($e instanceof BaseException) {
            return $e->render();
        } else if ($e instanceof QueryException) {
            if ($e->errorInfo[1] == 1062) {
                return Response::apiError(['code' => StatusCodes::VALIDATION_ERROR, 'message' => __('Validation error: Duplicate data error!')]);
            }
        }
        return $this->prepareJsonResponse($request, $e);



        //return $e;
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        return new JsonResponse(
            ['code' => StatusCodes::UNKNOWN_ERROR, 'message' => $e->getMessage(), 'error' => $this->convertExceptionToArray($e)],
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
