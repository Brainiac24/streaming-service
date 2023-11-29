<?php

namespace App\Models;

use App\Constants\Roles;
use Auth;
use Illuminate\Database\Eloquent\Builder;

class Chat extends BaseModel
{
    public $fillable = [
        'event_session_id',
        'message_channel',
        'question_channel',
        'is_messages_enabled',
        'is_question_messages_enabled',
        'is_question_moderation_enabled',
        'is_active',
    ];

    public function scopeCurrentAuthedUser(Builder $query): void
    {
        $query
            ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', function ($join) {
                $join
                    ->on('role_user.access_group_id', '=', 'access_groups.id')
                    ->where('role_user.user_id', Auth::id())
                    ->where(function ($query) {
                        $query
                            ->orWhere('role_user.role_id', '=', Roles::ADMIN)
                            ->orWhere('role_user.role_id', '=', Roles::MODERATOR);
                    });
            });
    }


    public function scopeCurrentAuthedUserIsAllowedByEventId(Builder $query, $eventId): void
    {
        $query
            ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
            ->join('events', function ($join) use ($eventId) {
                $join
                    ->on('events.id', '=', 'event_sessions.event_id')
                    ->where('event_sessions.id', '=', $eventId);
            })
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('role_user', function ($join) {
                $join
                    ->on('role_user.access_group_id', '=', 'access_groups.id')
                    ->where('role_user.user_id', Auth::id())
                    ->where(function ($query) {
                        $query
                            ->orWhere('role_user.role_id', '=', Roles::ADMIN)
                            ->orWhere('role_user.role_id', '=', Roles::MODERATOR);
                    });
            });
    }
}
