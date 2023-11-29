<?php

namespace App\Repositories\Chat;

use App\Constants\CacheKeys;
use App\Models\Chat;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use Cache;

class ChatRepository extends BaseRepository
{
    public function __construct(public Chat $chat)
    {
        parent::__construct($chat);
    }

    public function findByEventSessionId($eventSessionId)
    {
        $cacheKey = CacheKeys::chatByEventSessionIdKey($eventSessionId);
        $chat = CacheServiceFacade::get($cacheKey);
        if (!$chat) {
            $chat = $this->chat
                ->join('event_sessions', function ($join) use ($eventSessionId) {
                    $join
                        ->on('event_sessions.id', '=', 'chats.event_session_id')
                        ->where('event_sessions.id', '=', $eventSessionId);
                })
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->first(['chats.*']);

            CacheServiceFacade::tags([
                CacheKeys::eventSessionIdTag($eventSessionId),
                CacheKeys::chatIdTag($chat['id'])
            ])
                ->set($cacheKey,  $chat, config('cache.ttl'));
        }
        return $chat;
    }
    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->chat
            ->currentAuthedUser()
            ->where('chats.id', $id)
            ->first(['chats.*']);
    }

    public function findEventByChatId($chatId)
    {
        return $this->chat
            ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
            ->join('events', 'events.id', '=', 'event_sessions.event_id')
            ->where('chats.id', '=', $chatId)
            ->first(['events.*']);
    }

    public function findEventSessionByChatId($chatId)
    {
        $eventSession = Cache::get(CacheKeys::eventSessionByChatIdKey($chatId));
        if (!$eventSession) {
            $eventSession = $this->chat
                ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
                ->where('chats.id', '=', $chatId)
                ->first(['event_sessions.*']);
            CacheServiceFacade::tags([
                CacheKeys::eventSessionIdTag($eventSession['id']),
                CacheKeys::eventIdTag($eventSession['event_id'])
            ])
                ->set(CacheKeys::eventSessionByChatIdKey($chatId), $eventSession, config('cache.ttl'));
        }
        return $eventSession;
    }

    public function accessGroupIdByChatId($chatId)
    {
        return CacheServiceFacade::remember(CacheKeys::accessGroupByChatIdKey($chatId), config('cache.ttl'), function () use ($chatId) {
            return $this->chat
                ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->where('chats.id', '=', $chatId)
                ->first(['events.access_group_id'])
                ->access_group_id;
        });
    }

    public function findUserByChatId($chatId)
    {
        return $this->chat
            ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('projects',  'projects.id', '=', 'events.project_id')
            ->join('users',  'users.id', '=', 'projects.user_id')
            ->where('chats.id', '=', $chatId)
            ->first(['users.*']);
    }
}
