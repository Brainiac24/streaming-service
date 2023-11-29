<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\InitCloudPaymentRequest;
use App\Http\Resources\Payment\InitCloudPaymentResource;
use App\Services\Payment\CloudPaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CloudPaymentController extends Controller
{
    public function __construct(public CloudPaymentService $cloudPaymentService)
    {
    }

    /**
     * @throws Exception
     */
    public function init(InitCloudPaymentRequest $request): JsonResponse
    {
        return Response::apiSuccess(
            new InitCloudPaymentResource($this->cloudPaymentService->init($request->validated()))
        );
    }

    /**
     * @throws Exception
     */
    public function payNotification(Request $request): JsonResponse
    {
        return Response::apiSuccess(
            new InitCloudPaymentResource($this->cloudPaymentService->payNotify($request))
        );
    }

    public function failNotification(Request $request): \Illuminate\Http\Response
    {
        $this->cloudPaymentService->failNotify($request);
        return response()->noContent();
    }
}
