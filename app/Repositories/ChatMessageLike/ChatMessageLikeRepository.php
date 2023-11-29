<?php

namespace App\Repositories\ChatMessageLike;

use App\Constants\DynamicTablePrefixes;
use App\Models\ChatMessageLike;
use App\Repositories\BaseRepository;
use Auth;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class ChatMessageLikeRepository extends BaseRepository
{
    public function __construct(public ChatMessageLike $chatMessageLike)
    {
        parent::__construct($chatMessageLike);
    }

    public function listByChatId($chatId, $chatMessageId)
    {
        $chatMessageLikesTable = DynamicTablePrefixes::CHAT_MESSAGE_LIKES . $chatId;
        $chatMessageTable = DynamicTablePrefixes::CHAT_MESSAGES . $chatId;

        $page = (int)Request::get('page', 1);
        $perPage = (int)Request::get('perPage', 50);

        $subQuery = $this->chatMessageLike
            ->setTable($chatMessageLikesTable)
            ->orderBy($chatMessageLikesTable . '.id', 'desc')
            ->skip(--$page * $perPage)
            ->take($perPage + 1);

        return $this->chatMessageLike
            ->from($subQuery, $chatMessageLikesTable)
            ->join($chatMessageTable, $chatMessageTable . '.id', '=', $chatMessageLikesTable . '.chat_message_id')
            ->join('chats', 'chats.id', '=', $chatMessageTable . '.chat_id')
            ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('users as like_users', 'like_users.id', '=', $chatMessageLikesTable . '.user_id')
            ->join('role_user as like_role_user', function ($join) {
                $join
                    ->on('like_role_user.user_id', '=', 'like_users.id')
                    ->on('like_role_user.access_group_id', 'access_groups.id');
            })
            ->join('roles as like_roles', 'like_roles.id', '=', 'like_role_user.role_id')
            ->where('chat_message_id', $chatMessageId)
            ->get(
                [
                    $chatMessageLikesTable . '.*',
                    'like_users.id as like_user_id',
                    'like_users.name as like_user_name',
                    'like_users.lastname as like_user_lastname',
                    'like_users.email as like_user_email',
                    'like_users.avatar_path as like_user_avatar_path',
                    'like_roles.id as like_role_id',
                    'like_roles.name as like_role_name',
                    'like_roles.display_name as like_role_label',
                ]
            );
    }

    public function createByChatId(array $data, $chatId)
    {
        $this->chatMessageLike->setTable(table: DynamicTablePrefixes::CHAT_MESSAGE_LIKES . $chatId);

        $chatMessageLike = $this->chatMessageLike->where($data)->first();

        if ($chatMessageLike) {
            return false;
        }

        return $this->chatMessageLike->firstOrCreate($data);
    }

    function createTableByChatId($chatId)
    {
        $tableName = DynamicTablePrefixes::CHAT_MESSAGE_LIKES . $chatId;

        Schema::create($tableName, function (Blueprint $table) use ($chatId) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('chat_message_id')->constrained(DynamicTablePrefixes::CHAT_MESSAGES . $chatId);
            $table->timestamps();
        });
    }

    public function deleteByChatId($chatId, $chatMessageId)
    {
        $this->chatMessageLike->setTable(DynamicTablePrefixes::CHAT_MESSAGE_LIKES . $chatId);

        return $this->chatMessageLike
            ->where('chat_message_id', $chatMessageId)
            ->where('user_id', Auth::id())
            ->delete();
    }
}
