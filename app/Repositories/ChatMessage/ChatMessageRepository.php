<?php

namespace App\Repositories\ChatMessage;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\DynamicTablePrefixes;
use App\Models\ChatMessage;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class ChatMessageRepository extends BaseRepository
{

    public function __construct(public ChatMessage $chatMessage)
    {
        parent::__construct($chatMessage);
    }

    public function findByIdAndChatId($id, $chatId)
    {
        $this->chatMessage->setTable(table: DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        return parent::findById($id);
    }

    public function allPinnedByChatId($chatId)
    {
        $this->chatMessage->setTable(table: DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        return $this->chatMessage->where('is_pinned', true)->get();
    }

    public function updateAllIsPinnedToFalseByChatId($chatId)
    {
        $this->chatMessage->setTable(table: DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        return $this->chatMessage->where('is_pinned', true)->update(['is_pinned' => false]);
    }

    public function eventSessionByChatId($id, $chatId)
    {
        $this->chatMessage->setTable(table: DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        return parent::findById($id);
    }

    public function createByChatId(array $data, $chatId)
    {
        $this->chatMessage->setTable(table: DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        return parent::create($data);
    }

    public function updateByChatId(array $data, $id, $chatId)
    {
        $this->chatMessage->setTable(DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        return parent::update($data, $id);
    }

    public function incrementLikesCount($chatId, $id)
    {
        $this->chatMessage->setTable(DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        $chatMessage = $this->chatMessage->findOrFail($id);

        $chatMessage->likes_count = (int)$chatMessage->likes_count + 1;
        $chatMessage->save();

        return $chatMessage;
    }

    public function decrementLikesCount($chatId, $id)
    {
        $this->chatMessage->setTable(DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        $chatMessage = $this->chatMessage->findOrFail($id);

        if ($chatMessage->likes_count > 0) {
            $chatMessage->likes_count = (int)$chatMessage->likes_count - 1;
            $chatMessage->save();
        }

        return $chatMessage;
    }

    public function deleteByChatId($id, $chatId)
    {
        $this->chatMessage->setTable(DynamicTablePrefixes::CHAT_MESSAGES . $chatId);

        return parent::delete($id);
    }

    function createTableByChatId($chatId)
    {
        $tableName = DynamicTablePrefixes::CHAT_MESSAGES . $chatId;

        Schema::create($tableName, function (Blueprint $table) use ($tableName) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('chat_id')->constrained();
            $table->foreignId('reply_to_chat_message_id')->nullable()->constrained($tableName);
            $table->text('text');
            $table->integer('likes_count')->nullable();
            $table->boolean('is_pinned')->nullable();
            $table->boolean('is_answered')->nullable();
            $table->boolean('is_moderation_passed')->nullable();
            $table->foreignId('chat_message_type_id')->constrained();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function listByChatId($eventId, $eventSessionId, $chatId, $chatMessageTypeId, $isPinnedOnly = false)
    {
        $chatMessageTable = DynamicTablePrefixes::CHAT_MESSAGES . $chatId;
        $chatMessageLikesTable = DynamicTablePrefixes::CHAT_MESSAGE_LIKES . $chatId;

        $page = (int)Request::get('page', 1);
        $perPage = (int)Request::get('perPage', 50);
        $lastId = (int)Request::get('lastId', 0);
        $chatMessages = null;

        if (!$lastId) {
            $chatMessages = CacheServiceFacade::get(CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $chatMessageTypeId, $isPinnedOnly));
        }

        if (!$chatMessages || $page != 1) {
            $subQuery = $this->model
                ->setTable($chatMessageTable)
                ->where('chat_message_type_id', $chatMessageTypeId)
                ->when($isPinnedOnly, function ($query) use ($isPinnedOnly, $chatMessageTable) {
                    $query->where($chatMessageTable . '.is_pinned', $isPinnedOnly);
                })
                ->when($lastId, function ($query) use ($lastId) {
                    $query->where("id", "<", $lastId);
                })
                ->orderBy($chatMessageTable . '.id', 'desc')
                ->when($chatMessageTypeId != ChatMessageTypes::QUESTION, function ($query) use ($page, $perPage) {
                    $query
                        ->skip(--$page * $perPage)
                        ->take($perPage);
                });
            $chatMessages = $this->chatMessage
                ->from($subQuery, $chatMessageTable)
                ->join('chats', 'chats.id', '=', $chatMessageTable . '.chat_id')
                ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->join('users', 'users.id', '=', $chatMessageTable . '.user_id')
                ->leftJoin('role_user',  function ($join) {
                    $join
                        ->on('role_user.user_id', '=', 'users.id')
                        ->on('role_user.access_group_id', 'access_groups.id');
                })
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')

                ->leftJoin($chatMessageTable . ' as reply_chat_messages', 'reply_chat_messages.id', '=', $chatMessageTable . '.reply_to_chat_message_id')
                ->leftJoin('users as reply_users', 'reply_users.id', '=', 'reply_chat_messages.user_id')
                ->leftJoin('role_user as reply_role_user',  function ($join) {
                    $join
                        ->on('reply_role_user.user_id', '=', 'reply_users.id')
                        ->on('reply_role_user.access_group_id', 'access_groups.id');
                })
                ->leftJoin('roles as reply_roles', 'reply_roles.id', '=', 'reply_role_user.role_id')
                ->leftJoin($chatMessageLikesTable . ' as chat_message_likes', 'chat_message_likes.chat_message_id', '=', $chatMessageTable . '.id')
                ->when($isPinnedOnly, function ($query) use ($chatMessageTable) {
                    $query->orderBy($chatMessageTable . '.id', 'desc');
                }, function ($query) use ($chatMessageTable) {
                    $query->orderBy($chatMessageTable . '.id', 'asc');
                })
                ->get(
                    [
                        $chatMessageTable . '.*',
                        'users.id as user_id',
                        'users.name as user_name',
                        'users.lastname as user_lastname',
                        'users.email as user_email',
                        'users.avatar_path as user_avatar_path',

                        'roles.id as role_id',
                        'roles.name as role_name',
                        'roles.display_name as role_label',

                        'reply_chat_messages.user_id as reply_user_id',
                        'reply_chat_messages.chat_id as reply_chat_id',
                        'reply_chat_messages.reply_to_chat_message_id as reply_reply_to_chat_message_id',
                        'reply_chat_messages.text as reply_text',
                        'reply_chat_messages.likes_count as reply_likes_count',
                        'reply_chat_messages.is_pinned as reply_is_pinned',
                        'reply_chat_messages.is_answered as reply_is_answered',
                        'reply_chat_messages.is_moderation_passed as reply_is_moderation_passed',
                        'reply_chat_messages.chat_message_type_id as reply_chat_message_type_id',
                        'reply_chat_messages.created_at as reply_created_at',
                        'reply_chat_messages.updated_at as reply_updated_at',

                        'reply_users.id as reply_user_id',
                        'reply_users.name as reply_user_name',
                        'reply_users.lastname as reply_user_lastname',
                        'reply_users.email as reply_user_email',
                        'reply_users.avatar_path as reply_user_avatar_path',

                        'reply_roles.id as reply_role_id',
                        'reply_roles.name as reply_role_name',
                        'reply_roles.display_name as reply_role_label',

                        'chat_message_likes.user_id as chat_message_like_user_id'
                    ]
                );
            if ($chatMessages) {
                $chatMessages = $chatMessages->toArray();
            }
            if ($page == 1) {
                CacheServiceFacade::tags([
                    CacheKeys::eventIdTag($eventId),
                    CacheKeys::eventSessionIdTag($eventSessionId),
                    CacheKeys::chatIdTag($chatId)
                ])
                    ->set(
                        CacheKeys::chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $chatMessageTypeId, $isPinnedOnly),
                        $chatMessages,
                        config('cache.ttl')
                    );
            }
        }


        return $chatMessages;
    }

    public function listByChatIdWithoutPagination($chatId, $chatMessageTypeId)
    {
        $chatMessageTable = DynamicTablePrefixes::CHAT_MESSAGES . $chatId;

        $chatMessages = null;


        $subQuery = $this->model
            ->setTable($chatMessageTable)
            ->where('chat_message_type_id', $chatMessageTypeId)
            ->orderBy($chatMessageTable . '.id', 'desc');
        $chatMessages = $this->chatMessage
            ->from($subQuery, $chatMessageTable)
            ->join('chats', 'chats.id', '=', $chatMessageTable . '.chat_id')
            ->join('users', 'users.id', '=', $chatMessageTable . '.user_id')
            ->get(
                [
                    $chatMessageTable . '.text as message',
                    $chatMessageTable . '.created_at as datetime',
                    'users.id as user_id',
                    'users.name as name',
                    'users.lastname as lastname',
                    'users.email as email'
                ]
            );
        if ($chatMessages) {
            $chatMessages = $chatMessages->toArray();
        }
        return $chatMessages;
    }

    public function findByChatMessageId($chatId, $chatMessageId)
    {
        $chatMessageTable = DynamicTablePrefixes::CHAT_MESSAGES . $chatId;
        $chatMessageLikesTable = DynamicTablePrefixes::CHAT_MESSAGE_LIKES . $chatId;

        return $this->chatMessage
            ->setTable($chatMessageTable)
            ->join('chats', 'chats.id', '=', $chatMessageTable . '.chat_id')
            ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
            ->join('events',  'events.id', '=', 'event_sessions.event_id')
            ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
            ->join('users', 'users.id', '=', $chatMessageTable . '.user_id')
            ->leftJoin('role_user',  function ($join) {
                $join
                    ->on('role_user.user_id', '=', 'users.id')
                    ->on('role_user.access_group_id', 'access_groups.id');
            })
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')

            ->leftJoin($chatMessageTable . ' as reply_chat_messages', 'reply_chat_messages.id', '=', $chatMessageTable . '.reply_to_chat_message_id')
            ->leftJoin('users as reply_users', 'reply_users.id', '=', 'reply_chat_messages.user_id')
            ->leftJoin('role_user as reply_role_user',  function ($join) {
                $join
                    ->on('reply_role_user.user_id', '=', 'reply_users.id')
                    ->on('reply_role_user.access_group_id', 'access_groups.id');
            })
            ->leftJoin('roles as reply_roles', 'reply_roles.id', '=', 'reply_role_user.role_id')
            ->leftJoin($chatMessageLikesTable . ' as chat_message_likes', 'chat_message_likes.chat_message_id', '=', $chatMessageTable . '.id')
            ->where($chatMessageTable . '.id', '=', $chatMessageId)
            ->get(
                [
                    $chatMessageTable . '.*',
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.lastname as user_lastname',
                    'users.email as user_email',
                    'users.avatar_path as user_avatar_path',

                    'roles.id as role_id',
                    'roles.name as role_name',
                    'roles.display_name as role_label',

                    'reply_chat_messages.user_id as reply_user_id',
                    'reply_chat_messages.chat_id as reply_chat_id',
                    'reply_chat_messages.reply_to_chat_message_id as reply_reply_to_chat_message_id',
                    'reply_chat_messages.text as reply_text',
                    'reply_chat_messages.likes_count as reply_likes_count',
                    'reply_chat_messages.is_pinned as reply_is_pinned',
                    'reply_chat_messages.is_answered as reply_is_answered',
                    'reply_chat_messages.is_moderation_passed as reply_is_moderation_passed',
                    'reply_chat_messages.chat_message_type_id as reply_chat_message_type_id',
                    'reply_chat_messages.created_at as reply_created_at',
                    'reply_chat_messages.updated_at as reply_updated_at',

                    'reply_users.id as reply_user_id',
                    'reply_users.name as reply_user_name',
                    'reply_users.lastname as reply_user_lastname',
                    'reply_users.email as reply_user_email',
                    'reply_users.avatar_path as reply_user_avatar_path',

                    'reply_roles.id as reply_role_id',
                    'reply_roles.name as reply_role_name',
                    'reply_roles.display_name as reply_role_label',

                    'chat_message_likes.user_id as chat_message_like_user_id'
                ]
            );
    }
}
