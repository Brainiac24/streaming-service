<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentRequisiteRequest;
use App\Http\Resources\Payment\PaymentRequisite;
use App\Http\Resources\Payment\PaymentRequisiteListResource;
use App\Repositories\Payment\PaymentRequisiteRepository;
use App\Services\Payment\PaymentRequisiteService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class PaymentRequisiteController extends Controller
{
    public function __construct(
        public PaymentRequisiteService $paymentRequisiteService,
        public PaymentRequisiteRepository $paymentRequisiteRepository)
    {

    }

    public function index(): JsonResponse
    {
        return Response::apiSuccess(
            new PaymentRequisiteListResource($this->paymentRequisiteRepository->getUserPaymentRequisiteList())
        );
    }

    public function show($projectId): JsonResponse
    {
        return Response::apiSuccess(
            new PaymentRequisite($this->paymentRequisiteRepository->getPaymentRequisiteByProjectIdAndAuthUserId($projectId))
        );
    }

    public function store(PaymentRequisiteRequest $request): JsonResponse
    {
        return Response::apiSuccess(
            new PaymentRequisite($this->paymentRequisiteService->create($request->validated()))
        );
    }

    public function destroy($id): JsonResponse
    {
        $paymentRequisiteRequest = $this->paymentRequisiteRepository->getUserPaymentRequisiteById($id);
        $paymentRequisiteRequest->delete();

        return Response::apiSuccess();
    }
}
