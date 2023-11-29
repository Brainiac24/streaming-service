<?php

namespace App\Services\WebSocket;

use App\Http\Resources\BaseJsonResource;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Services\Helper\HelperService;
use Auth;
use phpcent\Client;

class WebSocketService
{
    public function __construct(
        public Client $webSocketClient,
        public ChatRepository $chatRepository,
        public EventRepository $eventRepository,
        public EventSessionRepository $eventSessionRepository
    ) {
        $this->webSocketClient->setApiKey(env('CENTRIFUGO_APIKEY'));
        $this->webSocketClient->setSafety(false);
    }

    public function generateConnectionToken($userId = null)
    {
        $userId ??= Auth::id();

        $this->webSocketClient->setSecret(env('JWT_SECRET_KEY'));
        $token = $this->webSocketClient->generateConnectionToken($userId);

        return $token;
    }

    public function publish($channels, $data)
    {
        if ($data instanceof BaseJsonResource) {
            $data = $data->toArray();
        }

        if ($data != null && !empty($data) && is_array($data)) {
            array_walk_recursive($data, HelperService::class . '::arrayRecursiveChangeDateFormat');
        }

        if (!is_string($channels) && !empty($channels)) {

            foreach ($channels as $channel) {
                $this->webSocketClient->publish($channel, $data);
            }

            return true;
        }

        return $this->webSocketClient->publish($channels, $data);
    }

    public function closeConnection($event, $userId = null)
    {

        $userId ??= Auth::id();
        $this->webSocketClient->setSecret(env('JWT_SECRET_KEY'));

        return $this->webSocketClient->publish(Auth::user()->channel, [
            "action" => "closeSession",
            "data" => [
                "event" => $event,
                "time" => time()
            ]
        ]);
    }

    public function publishByChatId($data, $chatId, $isQuestionChannel = false)
    {
        $chat = $this->chatRepository->findById($chatId);
        $channels = $chat['message_channel'];

        if ($isQuestionChannel) {
            $channels = $chat['question_channel'];
        }

        return $this->publish($channels, $data);
    }

    public function publishByEventSessionId($data, $eventSessionId, $isOnlyPrivateChannel = false)
    {
        $eventSession = $this->eventSessionRepository->findById($eventSessionId);

        $channels = [];

        if ($isOnlyPrivateChannel) {
            $channels = $eventSession['private_channel'];
        } else {
            $channels[] = $eventSession['channel'];
            $channels[] = $eventSession['private_channel'];
        }

        return $this->publish($channels, $data);
    }
}
