<?php

namespace App\Services\Payment;

use App\Enums\Payment\PaymentRequisitesServiceEnum;
use App\Enums\Payment\PaymentTypeEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Repositories\Event\EventRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Repositories\Payment\PaymentRequisiteRepository;
use App\Services\EventTicket\EventTicketService;
use Auth;
use Exception;
use Http;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CloudPaymentService
{
    private const NOTIFY_PAY_UPDATE_URL = 'site/notifications/pay/update';
    private const NOTIFY_FAIL_UPDATE_URL = 'site/notifications/fail/update';
    public function __construct(
        public PaymentRepository $paymentRepository,
        public EventRepository $eventRepository,
        public PaymentRequisiteRepository $paymentRequisiteRepository,
        public EventTicketService $eventTicketService
    )
    {

    }

    /**
     * Инициализирует новую запись о платеже на основе предоставленных данных запроса.
     *
     * @param array $request Ассоциативный массив, содержащий необходимые данные для инициализации платежа.
     *                       - 'event_id': Идентификатор события, для которого производится платеж.
     * @return int Идентификатор вновь созданной записи о платеже.
     * @throws Exception В случае проблемы с созданием записи о платеже.
     */
    public function init(array $request): int
    {
        // @todo Пока только оплата билета
        $event = $this->eventRepository->getCurrentAuthedUserByIsTicketSalesEnabled($request['event_id']);
        $projectRequisite = $this->paymentRequisiteRepository->getPaymentRequisiteByProjectIdAndService(
            $event->project_id,
            PaymentRequisitesServiceEnum::CLOUD_PAYMENTS
        );

        $paymentData = [
            'user_id' => Auth::user()->id,
            'event_id' => $request['event_id'],
            'status' => PaymentStatusEnum::NEW->value,
            'type' => PaymentTypeEnum::TICKETS->value,
            'payment_requisite_id' => $projectRequisite->id,
            'amount' => $event->ticket_price
        ];

        $payment = $this->paymentRepository->create($paymentData);

        return $payment->id;
    }

    /**
     * Обрабатывает уведомления о платежах и выполняет соответствующие действия.
     *
     * @param Request $request Запрос с данными о платеже.
     *
     * @return int
     * @throws Exception
     */
    public function payNotify(Request $request): int
    {
        if(!$request->InvoiceId) {
            throw new BadRequestHttpException();
        }

        try {

            $payment = $this->paymentRepository->findByIdNoFail($request->InvoiceId);

            $paymentRequisite = $this->paymentRequisiteRepository->findByIdNoFail($payment->payment_requisite_id);
            $hash = $this->checkHash($request->json()->all(), $paymentRequisite->private_api_key);

            // @todo После тестов убрать.
            Log::info("Формирования оплаты $payment->id", [
                'request' => $request->all(),
                'X-Content-HMAC' => $request->headers->get('X-Content-HMAC'),
                'hash' => $hash
            ]);

            if ($hash !== $request->headers->get('X-Content-HMAC')) {
                throw new BadRequestHttpException();
            }

            if($payment->type === PaymentTypeEnum::TICKETS->value && $payment->status === PaymentStatusEnum::NEW->value) {
                $ticket = $this->eventTicketService->generatePaidTicket($payment->event_id, $payment->user_id, $request->Amount);
                $request->merge(['ticket_id' => $ticket->id]);
                $data = [
                    'data_json' => $request->json()->all(),
                    'status' => PaymentStatusEnum::PAID->value
                ];

                $this->paymentRepository->update($data, $request->InvoiceId);
            }

            if((float) $payment->amount !== (float) $request->Amount) {
                $this->paymentRepository->update(['status' => PaymentStatusEnum::PARTLY_PAID], $payment->id);
            }

        } catch (Exception $e) {

            $data = [
                'data_json' => $request->json()->all(),
                'status' => PaymentStatusEnum::FAILED->value
            ];

            $this->paymentRepository->update($data, $request->InvoiceId);

            throw new Exception($e);
        }

        Log::info("Уведомления об оплате $payment->id", [
            'request' => $request->all(),
            'X-Content-HMAC' => $request->headers->get('X-Content-HMAC'),
            'hash' => $hash
        ]);

        return $payment->id;
    }


    /**
     * Генерирует хеш HMAC  с использованием SHA-256.
     * @param array $requestData
     * @param string $privateApiKey
     * @return string
     */
    public function checkHash(array $requestData, string $privateApiKey): string
    {
        $jsonData = json_encode($requestData);
        $hash = hash_hmac('sha256', $jsonData, $privateApiKey, true);

        return base64_encode($hash);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function failNotify(Request $request): void
    {
        $data = [
            'data_json' => $request->json()->all(),
            'status' => PaymentStatusEnum::FAILED->value
        ];

        $this->paymentRepository->update($data, $request->InvoiceId);

        Log::error('Ошибка при оплате CloudPayments', $request);
    }


    /**
     * @param string $publicApiKey
     * @param string $privateApiKey
     * @return bool
     */
    public function checkApiKey(string $publicApiKey, string $privateApiKey): bool
    {
        try {
            $response = Http::withBasicAuth($publicApiKey, $privateApiKey)
                ->post(config('services.cloudpayments.host_api'). 'test');

            return $response->json()['Success'];
        } catch (Exception $e) {
            Log::error($e);
        }

        return false;
    }


    /**
     * @param string $publicApiKey
     * @param string $privateApiKey
     * @return bool
     */
    public function updateNotifyType(string $publicApiKey, string $privateApiKey): bool
    {
        try {

            $responsePay = Http::withBasicAuth($publicApiKey, $privateApiKey)
                ->post(config('services.cloudpayments.host_api'). self::NOTIFY_PAY_UPDATE_URL, [
                    'IsEnabled' => true,
                    'Address' => config('services.cloudpayments.notification_pay_callback_url'),
                    'HttpMethod' => 'POST',
                    'Encoding' => 'UTF8',
                    'Format' => 'CloudPayments',
                ]);

            $responseFail = Http::withBasicAuth($publicApiKey, $privateApiKey)
                ->post(config('services.cloudpayments.host_api'). self::NOTIFY_FAIL_UPDATE_URL, [
                    'IsEnabled' => true,
                    'Address' => config('services.cloudpayments.notification_fail_callback_url'),
                    'HttpMethod' => 'POST',
                    'Encoding' => 'UTF8',
                    'Format' => 'CloudPayments',
                ]);

            return $responsePay && $responseFail;


        } catch (Exception $e) {
            Log::error($e);
        }

        return false;
    }
}
