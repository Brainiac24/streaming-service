<?php

namespace App\Services\ChatMessageLike;

use App\Repositories\ChatMessage\ChatMessageRepository;
use App\Repositories\ChatMessageLike\ChatMessageLikeRepository;
use App\Services\Cache\CacheServiceFacade;
use Auth;

class ChatMessageLikeService
{
    public function __construct(
        public ChatMessageLikeRepository $chatMessageLikeRepository,
        public ChatMessageRepository $chatMessageRepository
    ) {
    }

    public function listByChatId($chatId, $chatMessageId)
    {
        $chatMessageLikes = $this->chatMessageLikeRepository->listByChatId($chatId, $chatMessageId)->toArray();
        $chatMessageLikeResult = [];
        $users = [];
        $user = [];
        foreach ($chatMessageLikes as $chatMessageLike) {

            if (!isset($chatMessageLikeResult[$chatMessageLike['chat_message_id']])) {
                $chatMessageLikeResult[$chatMessageLike['chat_message_id']] = $chatMessageLike;
            }

            if (!isset($chatMessageLikeResult[$chatMessageLike['chat_message_id']]['users'][$chatMessageLike['like_user_id']])) {
                $users = &$chatMessageLikeResult[$chatMessageLike['chat_message_id']]['users'];
                $user = &$users[$chatMessageLike['like_user_id']];
                $user = [
                    'id' => $chatMessageLike['like_user_id'],
                    'name' => $chatMessageLike['like_user_name'],
                    'lastname' => $chatMessageLike['like_user_lastname'],
                    'fullname' => trim($chatMessageLike['like_user_name'] . ' ' . $chatMessageLike['like_user_lastname']),
                    'email' => $chatMessageLike['like_user_email'],
                    'avatar_path' => $chatMessageLike['like_user_avatar_path'],
                    'chat_message_like_created_at' => $chatMessageLike['created_at']
                ];
            }

            $roles = &$user['roles'];
            $roles[$chatMessageLike['like_role_id']] = [
                'id' => $chatMessageLike['like_role_id'],
                'name' => $chatMessageLike['like_role_name'],
                'label' => $chatMessageLike['like_role_label'],
            ];
            $roles = array_values($roles);
        }

        $users = array_values($users);

        return $users;
    }

    public function createByChatId($chatId, $chatMessageId)
    {
        try {
            $data = [
                'chat_message_id' => $chatMessageId,
                'user_id' => Auth::id(),
            ];
            $chatMessageLike = $this->chatMessageLikeRepository->createByChatId($data, $chatId);

            

            if (!$chatMessageLike) {
                return false;
            }
            $this->chatMessageRepository->incrementLikesCount($chatId, $chatMessageId);
        } catch (\Illuminate\Database\QueryException $e) {
            if (strpos($e->getMessage(), 'Base table or view not found')) {
                $this->chatMessageLikeRepository->createTableByChatId($chatId);
                $chatMessageLike = $this->chatMessageLikeRepository->create($data);
            } else {
                throw $e;
            }
        }

        return $chatMessageLike;
    }

    public function deleteByChatId($chatId, $chatMessageId)
    {
        $chatMessageLike = $this->chatMessageLikeRepository->deleteByChatId($chatId, $chatMessageId);

        if ($chatMessageLike) {
            $this->chatMessageRepository->decrementLikesCount($chatId, $chatMessageId);
        }

        return $chatMessageLike;
    }
}
