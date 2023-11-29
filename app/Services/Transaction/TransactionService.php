<?php

namespace App\Services\Transaction;

use App\Constants\TransactionCodes;
use App\Constants\TransactionTypes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Fare\FareListResource;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Fare\FareRepository;
use App\Repositories\FareType\FareTypeRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\User\UserService;
use App\Services\WebSocket\WebSocketService;
use Auth;

class TransactionService
{

    public function __construct(
        public TransactionRepository $transactionRepository,
        public UserService $userService,
        public WebSocketService $webSocketService,
        public EventRepository $eventRepository,
        public EventSessionRepository $eventSessionRepository,
        public FareRepository $fareRepository,
        public FareTypeRepository $fareTypeRepository
    ) {
    }
    public function pay($userId, $transactionCodeId, $amount, $configs)
    {
        $transaction = $this->transactionRepository->create([
            'user_id' => $userId,
            'transaction_code_id' => $transactionCodeId,
            'transaction_type_id' => TransactionTypes::PAY,
            'amount' => $amount,
            'config_json' => $configs
        ]);

        $user = $this->userService->substractFromBalance($userId, $amount);

        $socketData = new BaseJsonResource(
            data: [
                "balance" => $user['balance']
            ],
            mutation: WebSocketMutations::SOCK_UPDATE_BALANCE,
            scope: WebSocketScopes::CMS
        );
        $this->webSocketService->publish($user->channel, $socketData);

        return $transaction;
    }

    public function fill($userId, $amount, $configs)
    {
        $transaction = $this->transactionRepository->create([
            'user_id' => $userId,
            'transaction_code_id' => TransactionCodes::FILL_BALANCE,
            'transaction_type_id' => TransactionTypes::FILL,
            'amount' => $amount,
            'config_json' => $configs
        ]);

        $user = $this->userService->addToBalance($userId, $amount);

        $socketData = new BaseJsonResource(
            data: [
                "balance" => $user['balance']
            ],
            mutation: WebSocketMutations::SOCK_UPDATE_BALANCE,
            scope: WebSocketScopes::CMS
        );
        $this->webSocketService->publish($user->channel, $socketData);

        return $transaction;
    }

    public function list($user_id = false)
    {
        if($user_id){
            $transactions = $this->transactionRepository->allWithPaginationByUserId($user_id);
        }else{
            $transactions = $this->transactionRepository->allWithPaginationForCurrentAuthedUser();
        }

        $fares = (new FareListResource($this->fareRepository->allWithFareType()))->toArray()['data'];
        $fares = collect(array_values([...$fares['stream_fares'], ...$fares['extra_stream']]))->keyBy('id');

        foreach ($transactions as $transaction) {
            $transaction['event'] = [];
            $transaction['event_session'] = [];
            if (isset($transaction['config_json']['event_id'])) {
                $event = $this->eventRepository->findById($transaction['config_json']['event_id']);
                $transaction['event'] = [
                    'id' => $event['id'],
                    'name' => $event['name']
                ];
            }
            if (isset($transaction['config_json']['event_session_id'])) {
                $eventSession = $this->eventSessionRepository->findById($transaction['config_json']['event_session_id']);
                $transaction['event_session'] = [
                    'id' => $eventSession['id'],
                    'name' => $eventSession['name'],
                ];
            }
            if (isset($transaction['config_json']['fare_id'])) {
                $transaction['fare'] = [
                    'id' => $fares[$transaction['config_json']['fare_id']]['id'],
                    'type' => $fares[$transaction['config_json']['fare_id']]['type'],
                    'name' => $fares[$transaction['config_json']['fare_id']]['name'],
                ];
            }
            if (isset($transaction['config_json']['service'])) {
            }
        }

        return $transactions;
    }
}
