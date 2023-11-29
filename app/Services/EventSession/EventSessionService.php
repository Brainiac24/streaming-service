<?php

namespace App\Services\EventSession;

use App\Constants\CacheKeys;
use App\Constants\ChatMessageTypes;
use App\Constants\EventSessionStatuses;
use App\Constants\ImagePlaceholders;
use App\Constants\Roles;
use App\Constants\TransactionCodes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Events\EventSessionUpdatedEvent;
use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Exceptions\User\BalanceNotEnoughException;
use App\Exceptions\ValidationException;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Poll\PollListResource;
use App\Repositories\ChatMessageType\ChatMessageTypeRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Fare\FareRepository;
use App\Repositories\PollStatus\PollStatusRepository;
use App\Repositories\PollType\PollTypeRepository;
use App\Repositories\SaleStatus\SaleStatusRepository;
use App\Repositories\StreamStatus\StreamStatusRepository;
use App\Services\Ban\BanService;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Chat\ChatService;
use App\Services\EventAccess\EventAccessService;
use App\Services\EventDataCollectionTemplate\EventDataCollectionTemplateService;
use App\Services\EventSessionVisit\EventSessionVisitService;
use App\Services\Fare\FareService;
use App\Services\Image\ImageService;
use App\Services\Poll\PollService;
use App\Services\Sale\SaleService;
use App\Services\Stream\StreamService;
use App\Services\Transaction\TransactionService;
use App\Services\WebSocket\WebSocketService;
use Auth;
use Illuminate\Support\Facades\File;
use Str;

class EventSessionService
{
    public function __construct(
        public EventSessionRepository $eventSessionRepository,
        public TransactionService $transactionService,
        public StreamService $streamService,
        public FareRepository $fareRepository,
        public EventRepository $eventRepository,
        public ImageService $imageService,
        public EventSessionVisitService $eventSessionVisitService,
        public EventAccessService $eventAccessService,
        public ChatService $chatService,
        public PollService $pollService,
        public SaleService $saleService,
        public BanService $banService,
        public ChatMessageTypeRepository $chatMessageTypeRepository,
        public PollStatusRepository $pollStatusRepository,
        public PollTypeRepository $pollTypeRepository,
        public SaleStatusRepository $saleStatusRepository,
        public StreamStatusRepository $streamStatusRepository,
        public EventDataCollectionTemplateRepository $eventDataCollectionTemplateRepository,
        public EventDataCollectionTemplateService $eventDataCollectionTemplateService,
        public WebSocketService $webSocketService,
        public FareService $fareService
    ) {
    }

    public function list($eventId)
    {
        return $this->eventSessionRepository->allWithChildByEventIdForCurrentAuthedUser($eventId);
    }

    public function findById($id)
    {
        $eventSession = $this->eventSessionRepository->findByIdForAuthedUser($id);
        //$this->eventSessionVisitService->create($eventSession->id);

        $chat = $this->chatService->getChatForStreamRoom($eventSession['event_id'], $eventSession['id'], $eventSession['config_json']['is_questions_enabled']);

        $configJson = $eventSession['config_json'];

        $eventSession['chat_id'] = $chat['id'];
        $configJson['is_messages_enabled'] = $chat['is_messages_enabled'];
        $configJson['is_question_messages_enabled'] = $chat['is_question_messages_enabled'];
        $configJson['is_question_moderation_enabled'] = $chat['is_question_moderation_enabled'];

        $eventSession['config_json'] = $configJson;

        return $eventSession;
    }

    public function findByKeyWithStream($key)
    {
        $eventSession = $this->eventSessionRepository->findByKeyWithEvent($key)->toArray();

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($eventSession['id']);

        if (!Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR])) {
            $this->fareService->checkFareUserConnectedCountIsNotExceededAndFail($eventSession);
        }

        if (!$eventSession['is_unique_ticket_enabled'] && !$eventSession['is_multi_ticket_enabled'] && !$eventSession['is_data_collection_enabled']) {
            $event = $this->eventRepository->findById($eventSession['event_id']);
            $this->eventAccessService->attachRoleToEventByUser(Auth::user(), $event, Roles::MEMBER);
        }

        if (Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER])) {
            return [
                'is_allowed_enter' => true,
                'data' => $this->findByIdWithStream($eventSession['id'], isWidget: true),
                'event_session_id' => $eventSession['id']
            ];
        }

        $eventSession['data_collection'] = $this->eventDataCollectionTemplateService->getTemplateWithUserDataByEventId($eventSession['event_id']);

        return [
            'is_allowed_enter' => false,
            'data' => $eventSession,
            'event_session_id' => $eventSession['id']
        ];
    }

    public function findByIdWithStream($id, $requestData = null, $isWidget = false)
    {

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($id);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        if (isset($requestData['project_link']) && !empty($requestData['project_link']) && !$this->eventSessionRepository->hasEventSessionByProjectLink($id, $requestData['project_link'])) {
            throw new NotFoundException();
        }
        $eventSession = $this->eventSessionRepository->findByIdWithStream($id);
        if (!$eventSession || empty($eventSession?->toArray())) {
            throw new NotFoundException();
        }

        if (!Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR])) {
            $this->fareService->checkFareUserConnectedCountIsNotExceededAndFail($eventSession[0]);
        }

        $eventSessionCount = $this->eventRepository->findSessionsCountById($eventSession[0]['event_id']);
        return $this->findForStream($eventSession, $eventSessionCount, $accessGroupId, $isWidget);
    }

    public function findByEventLinkAndSessionCodeWithStream($eventLink, $code, $requestData = null)
    {
        if (
            isset($requestData['project_link']) &&
            !empty($requestData['project_link']) &&
            !$this->eventSessionRepository->hasEventSessionByCodeAndEventLinkAndProjectLink($code, $eventLink, $requestData['project_link'])
        ) {
            throw new NotFoundException();
        }

        $eventSession = $this->eventSessionRepository->findByEventLinkAndSessionCodeWithStream($eventLink, $code);
        if (!$eventSession || empty($eventSession?->toArray())) {
            throw new NotFoundException();
        }

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($eventSession[0]['id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        if (!Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR])) {
            $this->fareService->checkFareUserConnectedCountIsNotExceededAndFail($eventSession[0]);
        }

        $eventSessionCount = $this->eventRepository->findSessionsCountById($eventSession[0]['event_id']);
        return $this->findForStream($eventSession,  $eventSessionCount, $accessGroupId);
    }

    public function findForStream($eventSession, $eventSessionCount, $accessGroupId, $isWidget = false)
    {
        $eventSessionResult = [];
        $eventSessionId = null;
        $eventId = null;
        $nimbleConfig = config('services.nimble');
        foreach ($eventSession as $eventSessionItem) {
            $eventSessionId = $eventSessionItem['id'];
            $eventId = $eventSessionItem['event_id'];
            if (!isset($eventSessionResult[$eventSessionId])) {
                $eventSessionResult[$eventSessionId] = $eventSessionItem->toArray();
            }

            $outputUrl = '';
            $streamOutput = json_decode($eventSessionItem['stream_output'], true);

            foreach ($nimbleConfig["edge_servers"] as $server) {
                if ($server['status']) {
                    $streamIsDvrEnabled = $isWidget ? $eventSessionItem['stream_is_dvr_out_enabled'] : $eventSessionItem['stream_is_dvr_enabled'];

                    if ($eventSessionItem['stream_is_fullhd_enabled']) {
                        $outputUrl = 'https://' . $server["host"] . ($streamIsDvrEnabled ? $streamOutput['dvr_fullhd_url'] : $streamOutput['fullhd_url']);
                    } else {
                        $outputUrl = 'https://' . $server["host"] . ($streamIsDvrEnabled ? $streamOutput['dvr_url'] : $streamOutput['url']);
                    }
                }
            }

            $eventSessionResult[$eventSessionId]['stream'] = [
                'id' => $eventSessionItem['stream_id'],
                'title' => $eventSessionItem['stream_title'],
                'cover_img_path' => ($eventSessionItem['stream_cover_img_path'] ?? $eventSessionItem['event_cover_img_path'] ?? null),
                'start_at' => $eventSessionItem['stream_start_at'],
                'user_connected_count' => $eventSessionItem['stream_user_connected_count'],
                'is_onair' => $eventSessionItem['stream_is_onair'],
                'is_dvr_enabled' => $eventSessionItem['stream_is_dvr_enabled'],
                'is_dvr_out_enabled' => $eventSessionItem['stream_is_dvr_out_enabled'],
                'is_fullhd_enabled' => $eventSessionItem['stream_is_fullhd_enabled'],
                'onair_at' => $eventSessionItem['stream_onair_at'],
                'url' => $outputUrl
            ];
        }

        $eventSessionResult[$eventSessionId]['roles'] = $this->eventAccessService->getRolesByCurrentUserAndAccessGroupId($accessGroupId);

        $eventSessionResult[$eventSessionId]['is_user_banned'] = $this->banService->isUserBannedForEvent($eventId);



        $chat = $this->chatService->getChatForStreamRoom($eventId, $eventSessionId,  $eventSessionResult[$eventSessionId]['config_json']['is_questions_enabled']);
        $eventSessionResult[$eventSessionId]['chat'] = $chat;

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($eventSessionId);

        $polls = [];
        if ($eventSessionResult[$eventSessionId]['config_json']['is_polls_enabled'] ?? false == true) {
            $polls = (new PollListResource($this->pollService->list($eventSessionId), $accessGroupId))?->toArray()['data'] ?? [];
        }
        $eventSessionResult[$eventSessionId]['polls'] = $polls;

        $sales = [];
        if ($eventSessionResult[$eventSessionId]['config_json']['is_sales_enabled'] ?? false == true) {
            $sales =  $this->saleService->list($eventSessionId) ?? [];
        }
        $eventSessionResult[$eventSessionId]['sales'] = $sales;

        $eventSessionResult[$eventSessionId]['config_json']['is_messages_enabled'] = $chat['is_messages_enabled'];
        $eventSessionResult[$eventSessionId]['config_json']['is_question_messages_enabled'] = $chat['is_question_messages_enabled'];
        $eventSessionResult[$eventSessionId]['config_json']['is_question_moderation_enabled'] = $chat['is_question_moderation_enabled'];

        $eventSessionResult[$eventSessionId]['chat_message_types'] = CacheServiceFacade::remember(CacheKeys::chatMessageTypesKey(), config('cache.ttl'), function () {
            return $this->chatMessageTypeRepository->all();
        });
        $eventSessionResult[$eventSessionId]['poll_statuses'] = CacheServiceFacade::remember(CacheKeys::pollStatusesKey(), config('cache.ttl'), function () {
            return $this->pollStatusRepository->all();
        });
        $eventSessionResult[$eventSessionId]['poll_types'] = CacheServiceFacade::remember(CacheKeys::pollTypesKey(), config('cache.ttl'), function () {
            return $this->pollTypeRepository->all();
        });
        $eventSessionResult[$eventSessionId]['sale_statuses'] = CacheServiceFacade::remember(CacheKeys::saleStatusesKey(), config('cache.ttl'), function () {
            return $this->saleStatusRepository->all();
        });
        $eventSessionResult[$eventSessionId]['stream_statuses'] = CacheServiceFacade::remember(CacheKeys::streamStatusesKey(), config('cache.ttl'), function () {
            return $this->streamStatusRepository->all();
        });

        $eventSessionResult[$eventSessionId]['is_only'] = $eventSessionCount > 1 ? false : true;

        $this->eventSessionVisitService->create($eventSessionId);

        return $eventSessionResult[$eventSessionId];
    }

    public function createEventSession($eventId, $eventSessionData)
    {
        $event = $this->eventRepository->findByIdForCurrentAuthedUser($eventId);
        $fare = $this->fareRepository->findById($eventSessionData['fare_id']);

        CacheServiceFacade::tags([CacheKeys::eventIdTag($eventId)])
            ->flush();

        return $this->create($event, Auth::user(), $fare, $eventSessionData);
    }

    public function updateEventSession($eventSessionId, $eventSessionData)
    {
        $chat = [];
        $oldChat = [];
        if (isset($eventSessionData['config'])) {

            $chat = $this->chatService->chatRepository->findByEventSessionId($eventSessionId);
            $oldChat = $chat->toArray();
            $isChatChanged = false;

            if (isset($eventSessionData['config']['is_messages_enabled'])) {
                if ($chat['is_messages_enabled'] != $eventSessionData['config']['is_messages_enabled']) {
                    $chat->is_messages_enabled = (bool)$eventSessionData['config']['is_messages_enabled'];
                    $isChatChanged = true;
                }
                unset($eventSessionData['config']['is_messages_enabled']);
            }

            if (isset($eventSessionData['config']['is_question_messages_enabled'])) {
                if ($chat['is_question_messages_enabled'] != $eventSessionData['config']['is_question_messages_enabled']) {
                    $chat->is_question_messages_enabled = (bool)$eventSessionData['config']['is_question_messages_enabled'];
                    $isChatChanged = true;
                }
                unset($eventSessionData['config']['is_question_messages_enabled']);
            }

            if (isset($eventSessionData['config']['is_question_moderation_enabled'])) {
                if ($chat['is_question_moderation_enabled'] != $eventSessionData['config']['is_question_moderation_enabled']) {
                    $chat->is_question_moderation_enabled = (bool)$eventSessionData['config']['is_question_moderation_enabled'];
                    $isChatChanged = true;
                }
                unset($eventSessionData['config']['is_question_moderation_enabled']);
            }

            if ($isChatChanged) {
                $chat->save();
                CacheServiceFacade::tags([
                    CacheKeys::chatIdTag($chat['id'])
                ])
                    ->flush();
            }

            $eventSessionData['config_json'] = $eventSessionData['config'];
            unset($eventSessionData['config']);
        }

        $oldEventSession = $this->eventSessionRepository->findById($eventSessionId);
        $eventSession = $this->eventSessionRepository->update($eventSessionData, $eventSessionId);

        CacheServiceFacade::tags([CacheKeys::eventSessionIdTag($eventSessionId)])
            ->flush();

        $event = $this->eventRepository->findByEventSessionId($eventSession['id']);

        CacheServiceFacade::forget(CacheKeys::eventSessionByKey($eventSession['key']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByIdForRoomKey($eventSession['id']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByCodeAndEventLink($eventSession['code'], $event['link']));

        EventSessionUpdatedEvent::dispatch($eventSession, $oldEventSession, $chat, $oldChat);

        $configJson = $eventSession['config_json'];
        $configJson['is_messages_enabled'] = $chat['is_messages_enabled'] ?? true;
        $configJson['is_question_messages_enabled'] = $chat['is_question_messages_enabled'] ?? false;
        $configJson['is_question_moderation_enabled'] = $chat['is_question_moderation_enabled'] ?? false;
        $eventSession['config_json'] = $configJson;
        return $eventSession;
    }


    public function upgradeFare($eventSessionId, $eventSessionData)
    {
        $oldEventSession = $this->eventSessionRepository->findById($eventSessionId);

        if (!$oldEventSession) {
            throw new ValidationException(__('Validation error: Event session not found!'));
        }

        if ($oldEventSession->event_session_id != null) {
            throw new ValidationException(__('Validation error: Fare for child event session can not be upgraded!'));
        }

        $currentFare = $this->fareRepository->findById($oldEventSession->fare_id);
        $newFare = $this->fareRepository->findById($eventSessionData['fare_id']);

        if (floatval($currentFare->price) > floatval($newFare->price)) {
            throw new ValidationException(__('Validation error: Fare could not be downgraded!'));
        }

        $user = Auth::user();
        if (floatval($user->balance) < floatval($newFare->price)) {
            throw new BalanceNotEnoughException();
        }

        $eventSession = $this->eventSessionRepository->updateByModel($eventSessionData, $oldEventSession);

        CacheKeys::forgetEventSessionKeys($eventSessionId);

        $event = $this->eventRepository->findByEventSessionId($eventSession['id']);

        CacheServiceFacade::forget(CacheKeys::eventSessionByKey($eventSession['key']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByIdForRoomKey($eventSession['id']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByCodeAndEventLink($eventSession['code'], $event['link']));

        $newFarePrice = floatval($newFare->price) - floatval($currentFare->price);

        $this->transactionService->pay($user->id, TransactionCodes::UPGRADE_FARE,  $newFarePrice, [
            'event_id' => $eventSession->event_id,
            'event_session_id' => $eventSession->id,
            'fare_id' => $newFare->id,
            'fare_type_id' => $newFare->fare_type_id,
            'old_fare_price' => $currentFare->price,
            'new_fare_price' => $newFare->price,
        ]);

        return $eventSession;
    }

    public function create($event, $user, $fare, $eventSessionData = null)
    {
        if (floatval($user->balance) < floatval($fare->price)) {
            throw new BalanceNotEnoughException();
        }

        $stream = $this->streamService->create($event, $eventSessionData);

        $data = [
            "event_id" => $event->id,
            "code" => $this->generateSessionCode($event->id,  $eventSessionData['parent_id'] ?? null),
            "name" => $eventSessionData['name'] ?? __('Broadcast'),
            "config_json" => [
                "is_questions_enabled" => false,
                "is_polls_enabled" => false,
                "is_sales_enabled" => false,
                "sales_title" => __('Proposals')
            ],
            "event_session_id" => $eventSessionData['parent_id'] ?? null,
            "event_session_status_id" => EventSessionStatuses::ACTIVE,
            "stream_id" => $stream->id,
            "key" => Str::lower(Str::random(16)),
            "channel" => Str::lower(Str::random(28)),
            "private_channel" => Str::lower(Str::random(28)),
            "fare_id" => $fare->id
        ];

        $eventSession = $this->eventSessionRepository->create($data);

        $transactionCodeId = TransactionCodes::CREATE_SESSION;

        if ($eventSessionData['parent_id'] ?? false) {
            $transactionCodeId = TransactionCodes::CREATE_EXTRA_SESSION;
        }

        $this->transactionService->pay($user->id, $transactionCodeId, floatval($fare->price), [
            'event_id' => $event->id,
            'event_session_id' => $eventSession->id,
            'fare_id' => $fare->id,
            'fare_type_id' => $fare->fare_type_id
        ]);

        $chat = $this->chatService->createWithMessagesAndLikes($eventSession, $fare);

        $configJson = $eventSession['config_json'];

        $configJson['is_messages_enabled'] = $chat['is_messages_enabled'];
        $configJson['is_question_messages_enabled'] = true;
        $configJson['is_question_moderation_enabled'] = false;

        $eventSession['config_json'] = $configJson;

        return $eventSession;
    }

    public function generateSessionCode($evenId, $parentId = null)
    {
        $parentCode = $parentId ? $this->eventSessionRepository->findById($parentId)->code : null;

        $code = '';
        $done = false;
        $codes = $this->eventSessionRepository->pluckCode($evenId);

        if ($parentCode) {
            $code = $parentCode . '_';
        }
        $index = 0;
        while (!$done) {
            $index++;
            $done = !in_array($code . $index, $codes);
        }
        $code .= $index;

        return $code;
    }

    public function updateLogoImg($eventRequestData, $id)
    {
        $this->deleteLogoImg($id);
        $logoImageFilePath = $this->imageService->storeFromFile(
            $eventRequestData['logo'],
            Auth::id(),
            [
                'name_prefix' => 'event_session_logo_',
                'w' => 640,
                'h' => 360,
            ]
        );
        $thumbnailName = 'tmb_' . pathinfo($logoImageFilePath, PATHINFO_FILENAME);
        $logoThumbnailImageFilePath = $this->imageService->storeFromFile(
            $eventRequestData['logo'],
            Auth::id(),
            [
                'w' => 640,
                'h' => 360,
                'name' => $thumbnailName,
            ]
        );

        $eventSession = $this->eventSessionRepository->update([
            'logo_img_path' => $logoImageFilePath,
        ], $id);

        CacheKeys::forgetEventSessionKeys($id);

        $event = $this->eventRepository->findByEventSessionId($eventSession['id']);

        CacheServiceFacade::forget(CacheKeys::eventSessionByKey($eventSession['key']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByIdForRoomKey($eventSession['id']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByCodeAndEventLink($eventSession['code'], $event['link']));

        return [
            'logo_img_path' => $logoImageFilePath,
            'logo_tmb_img_path' => $logoThumbnailImageFilePath,
        ];
    }

    public function deleteLogoImg($id)
    {
        $filePath = $this->eventSessionRepository->findByIdOnlyEventSession($id)->logo_img_path;
        if (!$filePath) {
            return true;
        }
        $file = str_replace('/storage/', '', $filePath);
        $fileTmb = dirname($file) . '/tmb_' . basename($filePath);

        File::delete(storage_path('app/public/' . $file));
        File::delete(storage_path('app/public/' . $fileTmb));

        $eventSession = $this->eventSessionRepository->update([
            'logo_img_path' => null,
        ], $id);

        CacheKeys::forgetEventSessionKeys($id);


        $event = $this->eventRepository->findByEventSessionId($eventSession['id']);

        CacheServiceFacade::forget(CacheKeys::eventSessionByKey($eventSession['key']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByIdForRoomKey($eventSession['id']));
        CacheServiceFacade::forget(CacheKeys::eventSessionByCodeAndEventLink($eventSession['code'], $event['link']));

        return $eventSession;
    }

    public function publishKickCurrentUserFromOtherSession($eventSessionId)
    {
        $socketData = new BaseJsonResource(
            data: [
                'session_token' => Auth::getDecodedJWTToken()->getSessionId(),
                'event_session_id' => $eventSessionId
            ],
            mutation: WebSocketMutations::SOCK_KICK_USER,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publish(Auth::user()->channel, $socketData);
    }

    public function findByIdWithEvent($id)
    {
        $eventSession = $this->eventSessionRepository->findByIdWithEvent($id)?->toArray();

        if (!$eventSession) {
            throw new BusinessLogicException();
        }

        $coverImgPath = $eventSession['stream_cover_img_path'];
        if (empty($coverImgPath)) {
            $coverImgPath = $eventSession['event_cover_img_path'];
            if (empty($coverImgPath)) {
                $coverImgPath = ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER;
            }
        }

        $eventSession['cover_img_path'] = $coverImgPath;
        $eventSession['name'] = !empty($eventSession['name']) ? $eventSession['name'] : $eventSession['event_name'];

        return $eventSession;
    }
}
