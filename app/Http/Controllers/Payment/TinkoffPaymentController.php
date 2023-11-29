<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\InitTinkoffPaymentRequest;
use App\Http\Resources\Payment\InitTinkoffPaymentResource;
use App\Services\Payment\TinkoffPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TinkoffPaymentController extends Controller
{

    static $actionPermissionMap = [
        'init' => ':init',
    ];

    public function __construct(public TinkoffPaymentService $tinkoffPaymentService)
    {
    }

    public function init(InitTinkoffPaymentRequest $request)
    {
        return Response::apiSuccess(
            new InitTinkoffPaymentResource($this->tinkoffPaymentService->init($request->amount))
        );
    }

    public function notificationWebhook(Request $request)
    {
        return Response::apiSuccess(
            new InitTinkoffPaymentResource($this->tinkoffPaymentService->notificationWebhook($request))
        );
    }
}
