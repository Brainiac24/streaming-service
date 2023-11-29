<?php

namespace App\Services\MessageBroker;

use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Mailing\MailingRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;

class MessageBrokerService
{
    public function __construct(public MailingRepository $mailingRepository, public EventSessionRepository $eventSessionRepository)
    {
    }

    public function sendRequest($method, $url, $options = [])
    {
        $headers = ['X-Auth-Token' => config('auth.message_broker_x_auth_token')];
        $clinet = new Client(['headers' => $headers]);
        $response = null;
        try {
            $response = $clinet->request($method, $url, $options);

            if ($response->getStatusCode() == Response::HTTP_OK) {
                return json_decode($response->getBody()->getContents(), true);
            }

            return [
                "status" => $response->getStatusCode(),
                "body" => $response->getBody()->getContents()
            ];
        } catch (GuzzleException $e) {

            if ($response?->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR) {
                return [
                    "message" => 'Error! ' . $e->getMessage()
                ];
            }

            throw $e;
        }
    }

    public function pause($uuid)
    {
        $apiLink = config('app.message_broker_host') . '/api/v1/email-notification/' . $uuid . '/pause';
        return $this->sendRequest('PUT', $apiLink);
    }

    public function resume($uuid)
    {
        $apiLink = config('app.message_broker_host') . '/api/v1/email-notification/' . $uuid . '/resume';
        return $this->sendRequest('PUT', $apiLink);
    }

    public function cancel($uuid)
    {
        $apiLink = config('app.message_broker_host') . '/api/v1/email-notification/' . $uuid . '/cancel';
        return $this->sendRequest('PUT', $apiLink);
    }

    public function findByBatchId($uuid)
    {
        $apiLink = config('app.message_broker_host') . '/api/v1/email-notification/' . $uuid;
        return $this->sendRequest('GET', $apiLink);
    }

    public function updateMessageTemplate($requestData, $uuid)
    {

        $apiLink = config('app.message_broker_host') . '/api/v1/email-notification/' . $uuid . '/message-template';
        return $this->sendRequest('PUT', $apiLink, ['body' => $requestData]);
    }

    public function updateSmtpCredentials($requestData, $uuid)
    {
        $apiLink = config('app.message_broker_host') . '/api/v1/email-notification/' . $uuid . '/smtp-credentials';
        return $this->sendRequest('PUT', $apiLink, ['body' => $requestData]);
    }
}
