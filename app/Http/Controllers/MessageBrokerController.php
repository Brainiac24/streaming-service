<?php

namespace App\Http\Controllers;


use App\Http\Requests\MessageBroker\UpdateSmtpCredentialsRequest;
use App\Http\Requests\MessageBroker\UpdateMessageTemplateRequest;
use App\Http\Resources\BaseJsonResource;
use App\Services\MessageBroker\MessageBrokerService;
use Response;

class MessageBrokerController extends Controller
{

    static $actionPermissionMap = [
        'pause' => 'MessageBrokerController:pause',
        'resume' => 'MessageBrokerController:resume',
        'cancel' => 'MessageBrokerController:cancel',
        'findByBatchId' => 'MessageBrokerController:findByBatchId',
        'updateMessageTemplate' => 'MessageBrokerController:updateMessageTemplate',
        'updateSmtpCredentials' => 'MessageBrokerController:updateSmtpCredentials',
    ];

    public function __construct(public MessageBrokerService $messageBrokerServiceervice)
    {
        //
    }

    function pause($uuid)
    {
        return Response::apiSuccess(
            $this->messageBrokerServiceervice->pause($uuid)
        );
    }

    function resume($uuid)
    {
        return Response::apiSuccess(
            $this->messageBrokerServiceervice->resume($uuid)
        );
    }

    function cancel($uuid)
    {
        return Response::apiSuccess(
            $this->messageBrokerServiceervice->cancel($uuid)
        );
    }

    function findByBatchId($uuid)
    {
        return Response::apiSuccess(
            $this->messageBrokerServiceervice->findByBatchId($uuid)
        );
    }

    function updateMessageTemplate(UpdateMessageTemplateRequest $request, $uuid)
    {
        return Response::apiSuccess(
            $this->messageBrokerServiceervice->updateMessageTemplate($request->validated(), $uuid)
        );
    }

    function updateSmtpCredentials(UpdateSmtpCredentialsRequest $request, $uuid)
    {
        return Response::apiSuccess(
            $this->messageBrokerServiceervice->updateSmtpCredentials($request->validated(), $uuid)
        );
    }
}
