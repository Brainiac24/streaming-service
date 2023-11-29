<?php

namespace App\Listeners;

use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Events\EventSessionUpdatedEvent;
use App\Http\Resources\BaseJsonResource;
use App\Services\WebSocket\WebSocketService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EventSessionUpdatedHandler
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public WebSocketService $webSocketService)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\EventSessionUpdatedEvent  $event
     * @return void
     */
    public function handle(EventSessionUpdatedEvent $event)
    {

        $config = $event->eventSession->config_json;
        $oldConfig = $event->oldEventSession->config_json;
        $chat = $event->chat;
        $oldChat = $event->oldChat;

        $data = [];

        if (($chat['is_messages_enabled'] ?? null) != ($oldChat['is_messages_enabled'] ?? null)) {

            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SET_CHAT_CONFIG,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'is_messages_enabled' => $chat['is_messages_enabled']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }

        /*if (($config['is_private_chat_message_enabled'] ?? null) != ($oldConfig['is_private_chat_message_enabled'] ?? null)) {
            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SESSION_QUESTIONS_STATUS,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'is_private_chat_message_enabled' => $config['is_private_chat_message_enabled']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }*/

        if (($config['is_polls_enabled'] ?? null) != ($oldConfig['is_polls_enabled'] ?? null)) {
            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SET_CHAT_CONFIG,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'is_polls_enabled' => $config['is_polls_enabled']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }

        if (($chat['is_question_messages_enabled'] ?? null) != ($oldChat['is_question_messages_enabled'] ?? null)) {
            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SET_CHAT_CONFIG,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'is_question_messages_enabled' => $chat['is_question_messages_enabled']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }

        if (($config['is_questions_enabled'] ?? null) != ($oldConfig['is_questions_enabled'] ?? null)) {
            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SET_CHAT_CONFIG,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'is_questions_enabled' => $config['is_questions_enabled']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }

        if (($chat['is_question_moderation_enabled'] ?? null) != ($oldChat['is_question_moderation_enabled'] ?? null)) {
            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SET_CHAT_CONFIG,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'is_question_moderation_enabled' => $chat['is_question_moderation_enabled']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }

        if (($config['is_sales_enabled'] ?? null) != ($oldConfig['is_sales_enabled'] ?? null)) {
            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SET_CHAT_CONFIG,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'is_sales_enabled' => $config['is_sales_enabled']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }

        if (($config['sales_title'] ?? null) != ($oldConfig['sales_title'] ?? null)) {
            $data = new BaseJsonResource(
                scope: WebSocketScopes::EVENT,
                mutation: WebSocketMutations::SOCK_SET_CHAT_CONFIG,
                meta: [
                    'event_session_id' => $event->eventSession->id,
                    'sales_title' => $config['sales_title']
                ]
            );

            $this->webSocketService->publish([
                $event->eventSession->channel,
                $event->eventSession->private_channel,
            ], $data);
        }
    }
}
