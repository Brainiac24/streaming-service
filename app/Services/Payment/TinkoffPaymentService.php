<?php

namespace App\Services\Payment;

use App\Constants\TinkoffPaymentStatuses;
use App\Repositories\TinkoffPayment\TinkoffPaymentRepository;
use App\Repositories\TinkoffPaymentNotification\TinkoffPaymentNotificationRepository;
use App\Services\Transaction\TransactionService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TinkoffPaymentService
{

    public function __construct(
        public TinkoffPaymentRepository $tinkoffPaymentRepository,
        public TinkoffPaymentNotificationRepository $tinkoffPaymentNotificationRepository,
        public TransactionService $transactionService
    ) {
    }

    public function init($amount)
    {
        $config = config('services.tinkoff');
        $user = Auth::user();

        $payment = [
            'user_id' => $user->id,
            'amount' => $amount,
            'tinkoff_payment_status_id' => TinkoffPaymentStatuses::INIT,
            'config_json' => [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
        ];

        $tinkoffPayment = $this->tinkoffPaymentRepository->create($payment);

        $requestData = [
            "TerminalKey" => $config["terminal_key"],
            "Amount" => $amount * 100,
            "OrderId" => $tinkoffPayment->id,
            "SuccessURL" => $config["success_url"] . '?order=' . $tinkoffPayment->id,
            "NotificationURL" => $config["notification_url"],
            "DATA" => $payment['config_json'],
        ];

        $headers = [
            'Authorization' => 'Bearer TinkoffOpenApiSandboxSecretToken'
        ];

        $tinkoffPayment->request = $requestData;
        $tinkoffPayment->save();

        $response = Http::withHeaders($headers)->post(
            $config["api_url"] . 'Init',
            $requestData
        );

        $tinkoffPayment->response = json_encode($response);
        $tinkoffPayment->save();

        if (!$response->ok()) {
            throw new BadRequestHttpException(json_encode($response));
        }

        $responseJson = $response->json();

        if (($responseJson["Status"] ?? false) != TinkoffPaymentStatuses::NEW_NAME) {
            throw new BadRequestHttpException(json_encode($response));
        };

        $updateTinkofPaymentData = [
            "tinkoff_payment_status_id" => TinkoffPaymentStatuses::NEW,
            "response_payment_id" => $responseJson["PaymentId"],
            "response_payment_url" => $responseJson["PaymentURL"],
            "response" => json_encode($responseJson)
        ];

        $tinkoffPayment = $this->tinkoffPaymentRepository->update($updateTinkofPaymentData, $tinkoffPayment->id);

        return $responseJson["PaymentURL"];
    }


    public function notificationWebhook(Request $request)
    {
        $hash = $this->getTinkoffHash($request->all());

        if ($hash !== $request->Token) {
            throw new BadRequestHttpException();
        }

        $tinkoffPaymentNotification = $this->tinkoffPaymentNotificationRepository->create([
            "request" => json_encode($request->all()),
            "response_payment_id" => $request->PaymentId,
            "tinkoff_payment_id" => $request->OrderId,
            "tinkoff_payment_status_id" => constant(TinkoffPaymentStatuses::class . '::' . $request->Status)
        ]);

        if ($request->Status === TinkoffPaymentStatuses::CONFIRMED_NAME) {

            $tinkoffPaymentNotification = $this->tinkoffPaymentNotificationRepository->findByTinkoffPaymentStatusId($request->PaymentId, TinkoffPaymentStatuses::CONFIRMED);

            if (!$tinkoffPaymentNotification) {
                $this->transactionService->fill(
                    $tinkoffPaymentNotification->tinkoffPayment->user_id,
                    $tinkoffPaymentNotification->tinkoffPayment->amount,
                    [
                        'tinkoff_notification_id' => $tinkoffPaymentNotification->id,
                        "service" => "tinkoff"
                    ]
                );
            }
        }

        return 'OK';
    }

    public function getTinkoffHash($data)
    {
        $config = config('services.tinkoff');
        $data["Password"] = $config["password"];
        $str = '';
        ksort($data);
        foreach ($data as $k => $value) {
            if (!in_array($k, ["Token", "Shops", "Receipt", "DATA"])) $str .= var_export($value, true);
        }
        $str = str_replace("'", "", $str);
        return hash('sha256', $str);
    }
}
