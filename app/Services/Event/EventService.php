<?php

namespace App\Services\Event;

use App\Constants\CacheKeys;
use App\Constants\ContactGroups;
use App\Constants\EventStatuses;
use App\Constants\EventTicketTypes;
use App\Constants\ImagePlaceholders;
use App\Constants\Roles;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Exceptions\User\BalanceNotEnoughException;
use App\Exceptions\ValidationException;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\ContactGroup\ContactGroupRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventDataCollection\EventDataCollectionRepository;
use App\Repositories\EventDataCollectionTemplate\EventDataCollectionTemplateRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\EventTicket\EventTicketRepository;
use App\Repositories\Fare\FareRepository;
use App\Repositories\Mailing\MailingRepository;
use App\Repositories\MailingRequisite\MailingRequisiteRepository;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\RoleUser\RoleUserRepository;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\EventAccess\EventAccessService;
use App\Services\EventSession\EventSessionService;
use App\Services\EventTicket\EventTicketService;
use App\Services\Image\ImageService;
use App\Services\Mailing\MailingService;
use App\Services\Transaction\TransactionService;
use App\Services\WebSocket\WebSocketService;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Str;

class EventService
{
    public function __construct(
        public EventRepository $eventRepository,
        public EventSessionRepository $eventSessionRepository,
        public FareRepository $fareRepository,
        public ProjectRepository $projectRepository,
        public EventSessionService $eventSessionService,
        public TransactionService $transactionService,
        public ImageService $imageService,
        public WebSocketService $webSocketService,
        public EventAccessService $eventAccessService,
        public EventDataCollectionTemplateRepository $eventDataCollectionTemplateRepository,
        public EventDataCollectionRepository $eventDataCollectionRepository,
        public EventTicketRepository $eventTicketRepository,
        public UserRepository $userRepository,
        public EventTicketService $eventTicketService,
        public RoleUserRepository $roleUserRepository,
        public MailingService $mailingService,
        public MailingRequisiteRepository $mailingRequisiteRepository,
        public ContactGroupRepository $contactGroupRepository
    ) {}

    public function create($eventRequestData)
    {
        $user = Auth::user();
        $this->projectRepository->findByIdForCurrentAuthedUser($eventRequestData['project_id']);

        $fare = $this->fareRepository->findById($eventRequestData['fare_id']);

        if (floatval($user->balance) < floatval($fare->price)) {
            throw new BalanceNotEnoughException();
        }

        $eventRequestData['user_id'] = $user->id;
        $eventRequestData['event_status_id'] = EventStatuses::NEW;
        $eventRequestData['link'] = 'event_' . Str::lower(Str::random(16));

        $eventRequestData['start_at'] = Carbon::createFromTimestamp($eventRequestData['start_at'])->format('Y-m-d H:i:s');
        $eventRequestData['end_at'] = Carbon::createFromTimestamp($eventRequestData['end_at'])->format('Y-m-d H:i:s');

        $date = $eventRequestData['start_at'];

        $event = $this->eventRepository->create($eventRequestData);

        $this->eventAccessService->attachRoleToEventByUser(Auth::user(), $event, Roles::ADMIN);

        $eventSession = $this->eventSessionService->create($event, $user, $fare, $eventRequestData);

        $this->contactGroupRepository->create([
            'name' => ContactGroups::GENERAL,
            'event_id' => $event['id'],
            'is_common' => ContactGroups::IS_COMMON_TRUE
        ]);

        $mailingRequisits = $this->mailingRequisiteRepository->list($event['project_id']);

        $templates = config('mail.mailing_templates');

        foreach ($templates as $template) {
            $mailing['mailing_status_id'] = $template['mailing_status_id'];
            $mailing['message_template_id'] = $template['message_template_id'];
            $mailing['message_title'] = __($template['message_title']);
            $mailing['event_id'] = $event['id'];
            $mailing['event_session_id'] = $eventSession['id'];
            $mailing['project_id'] = $event['project_id'];
            $mailing['is_default'] = $template['is_default'];
            $mailing['delay_count'] = $template['delay_count'];
            $mailing['send_at'] = Carbon::parse($date)->subMinutes($template['delay_count'])->format("Y-m-d H:i:s");
            $mailing['mailing_requisite_id'] = null;
            if (count($mailingRequisits) > 0) {
                $mailing['mailing_requisite_id'] = $mailingRequisits[0]['id'];
            }
            $this->mailingService->create($mailing);
        }

        $this->eventAccessService->attachRoleToEventByUser(Auth::user(), $event, Roles::ADMIN);

        return $event;
    }

    public function update($eventRequestData, $id)
    {
        if (isset($eventRequestData['link']) && !empty($eventRequestData['link'])) {
            $event = $this->eventRepository->findByLink($eventRequestData['link']);

            if ($event && $event['id'] != $id) {
                throw new ValidationException('Validation error: Event with the same link already exists!');
            }
        }

        $event = $this->eventRepository->findByIdForCurrentAuthedUser($id);

        if (isset($eventRequestData['is_multi_ticket_enabled'])) {
            if ($event['is_multi_ticket_enabled'] != $eventRequestData['is_multi_ticket_enabled']) {
                if ($eventRequestData['is_multi_ticket_enabled'] == true) {
                    $this->roleUserRepository->deleteByRoleIdAndEventTicketIsNull(Roles::MEMBER, $event['access_group_id']);
                } else {
                    $this->roleUserRepository->deleteByRoleIdAndEventTicketIsNullAndTicketTypeId(Roles::MEMBER, $event['access_group_id'], EventTicketTypes::MULTI);
                }
            }
        }

        if (isset($eventRequestData['is_unique_ticket_enabled'])) {
            if ($event['is_unique_ticket_enabled'] != $eventRequestData['is_unique_ticket_enabled']) {
                if ($eventRequestData['is_unique_ticket_enabled'] == true) {
                    $this->roleUserRepository->deleteByRoleIdAndEventTicketIsNull(Roles::MEMBER, $event['access_group_id']);
                    $eventTickets = $this->eventTicketRepository->getUsersByEventIdAndUniqueType($event['id']);
                    foreach ($eventTickets as $eventTicket) {
                        $user = $this->userRepository->findByIdNoFail($eventTicket['user_id']);
                        if ($user) {
                            $user->attachRoleAndEventTicketToAccessGroup(Roles::MEMBER, $eventTicket['id'], $event['access_group_id']);
                        }
                    }
                } else {
                    $this->roleUserRepository->deleteByRoleIdAndEventTicketIsNullAndTicketTypeId(Roles::MEMBER, $event['access_group_id'], EventTicketTypes::UNIQUE);
                }
            }
        }
        $eventWithPaymentRequisite = $this->eventRepository->findByIdForCurrentAuthedUserAndRequisite($id);

        if (!$eventWithPaymentRequisite->payment_requisites_status) {
            unset($eventRequestData['is_ticket_sales_enabled']);
            unset($eventRequestData['ticket_price']);
        }

        $event = $this->eventRepository->updateByModelForCurrentAuthedUser($eventRequestData, $event);

        CacheServiceFacade::forget(CacheKeys::eventSessionsByEventIdKey($id));
        CacheServiceFacade::tags(CacheKeys::eventIdTag($id))->flush();

        $eventSessions = $this->eventSessionRepository->findByEventId($event['id']);

        foreach ($eventSessions as $eventSession) {
            CacheServiceFacade::forget(CacheKeys::eventByEventSessionIdKey($eventSession['id']));
            CacheServiceFacade::tags(CacheKeys::eventSessionIdTag($eventSession['id']))->flush();
        }

        return $eventWithPaymentRequisite;
    }

    public function upcoming()
    {
        $notEndedEvents = $this->eventRepository->upcomingNotEnded();

        $notEnded = $this->upcomingConstructor($notEndedEvents);

        return $notEnded;
    }

    public function archive()
    {
        $notEndedEvents = $this->eventRepository->upcomingNotEnded();
        $endedEvents = $this->eventRepository->upcomingEnded();

        $ended = $this->upcomingConstructor($endedEvents, true);

        $notEndedEventKeys = [];
        foreach ($notEndedEvents as $notEndedEvent) {
            $notEndedEventKeys[$notEndedEvent['id']] = $notEndedEvent['id'];
        }

        $ended = array_filter($ended, fn ($endedKey) => !isset($notEndedEventKeys[$ended[$endedKey]['id']]), ARRAY_FILTER_USE_KEY);

        return $ended;
    }

    public function upcomingConstructor($events, $isEndedEvents = false)
    {
        $data = [];

        foreach ($events as $event) {
            if (!isset($data[$event['id']])) {
                $data[$event['id']] = [
                    'id' => $event['id'],
                    'name' => $event['name'],
                    'link' => $event['link'],
                    'project_link' => $event['project_link'],
                ];
            }

            if (!isset($data[$event['id']]['event_session'][$event['event_session_id']])) {
                $eventSession = &$data[$event['id']]['event_session'];

                $eventSession[$event['event_session_id']] = [
                    'id' => $event['event_session_id'],
                    'name' => $event['event_session_name'],
                    'code' => $event['event_session_code'],
                    'stream' => [
                        'stream_id' => $event['stream_id'],
                        'start_at' => $event['stream_start_at'],
                        'onair_at' => $event['stream_onair_at'],
                        'is_onair' => $event['stream_is_onair'],
                        'cover_img_path' => $event['stream_cover_img_path'] ?? $event['cover_img_path'],
                    ],
                ];

                if (!isset($eventSession[$event['event_session_id']]['roles'][$event['role_id']])) {
                    $eventSession[$event['event_session_id']]['roles'][$event['role_id']] = [
                        'id' => $event['role_id'],
                        'name' => $event['role_name'],
                        'label' => __($event['role_display_name']),
                    ];
                }
            } else {
                $eventSession = &$data[$event['id']]['event_session'];

                if ($isEndedEvents) {
                    if (Carbon::parse($eventSession[$event['event_session_id']]['stream']['start_at']) <= Carbon::parse($event['stream_start_at'])) {
                        $eventSession[$event['event_session_id']] = [
                            'id' => $event['event_session_id'],
                            'name' => $event['event_session_name'],
                            'code' => $event['event_session_code'],
                            'stream' => [
                                'stream_id' => $event['stream_id'],
                                'start_at' => $event['stream_start_at'],
                                'onair_at' => $event['stream_onair_at'],
                                'is_onair' => $event['stream_is_onair'],
                                'cover_img_path' => $event['stream_cover_img_path'] ?? $event['cover_img_path'],
                            ],
                        ];

                        if (!isset($eventSession[$event['event_session_id']]['roles'][$event['role_id']])) {
                            $eventSession[$event['event_session_id']]['roles'][$event['role_id']] = [
                                'id' => $event['role_id'],
                                'name' => $event['role_name'],
                                'label' => __($event['role_display_name']),
                            ];
                        }
                    }
                } else {
                    $prevStartAt = Carbon::parse($eventSession[$event['event_session_id']]['stream']['start_at'])->timestamp;
                    $startAt = Carbon::parse($event['stream_start_at'])->timestamp;

                    if ($prevStartAt > $startAt && ($startAt > now()->timestamp || $event['stream_is_onair'])) {
                        $eventSession[$event['event_session_id']] = [
                            'id' => $event['event_session_id'],
                            'name' => $event['event_session_name'],
                            'code' => $event['event_session_code'],
                            'stream' => [
                                'stream_id' => $event['stream_id'],
                                'start_at' => $startAt,
                                'onair_at' => $event['stream_onair_at'],
                                'is_onair' => $event['stream_is_onair'],
                                'cover_img_path' => $event['stream_cover_img_path'] ?? $event['cover_img_path'],
                            ],
                        ];
                    }

                    if (!isset($eventSession[$event['event_session_id']]['roles'][$event['role_id']])) {
                        $eventSession[$event['event_session_id']]['roles'][$event['role_id']] = [
                            'id' => $event['role_id'],
                            'name' => $event['role_name'],
                            'label' => __($event['role_display_name']),
                        ];
                    }
                }
            }
        }

        $data = array_values($data);

        foreach ($data as &$item) {
            $item['event_session'] = array_values($item['event_session'])[0];
            $item['event_session']['roles'] = array_values($item['event_session']['roles']);
        }

        if ($isEndedEvents) {
            uksort($data, function ($a, $b) use ($data) {
                return Carbon::parse($data[$a]['event_session']['stream']['start_at'])->timestamp < Carbon::parse($data[$b]['event_session']['stream']['start_at'])->timestamp;
            });
        } else {
            uksort($data, function ($a, $b) use ($data) {
                return Carbon::parse($data[$a]['event_session']['stream']['start_at'])->timestamp > Carbon::parse($data[$b]['event_session']['stream']['start_at'])->timestamp;
            });
        }

        return $data;
    }

    public function updateCoverImg($eventRequestData, $id)
    {
        $coverImageFilePath = $this->imageService->storeFromFile($eventRequestData['cover'], Auth::id(), ['name_prefix' => 'event_cover_']);
        $thumbnailName = 'tmb_' . pathinfo($coverImageFilePath, PATHINFO_FILENAME);
        $coverThumbnailImageFilePath = $this->imageService->storeFromFile($eventRequestData['cover'], Auth::id(), [
            'w' => 256,
            'h' => 256,
            'name' => $thumbnailName,
        ]);

        $event = $this->eventRepository->update(
            [
                'cover_img_path' => $coverImageFilePath,
            ],
            $id,
        );

        CacheServiceFacade::forget(CacheKeys::eventByEventSessionIdKey($event['id']));

        return [
            'cover_img_path' => $coverImageFilePath,
            'cover_tmb_img_path' => $coverThumbnailImageFilePath,
        ];
    }

    public function updateLogoImg($eventRequestData, $id)
    {
        $this->deleteLogoImg($id);
        $logoImageFilePath = $this->imageService->storeFromFile($eventRequestData['logo'], Auth::id(), ['name_prefix' => 'event_logo_']);
        $thumbnailName = 'tmb_' . pathinfo($logoImageFilePath, PATHINFO_FILENAME);
        $logoThumbnailImageFilePath = $this->imageService->storeFromFile($eventRequestData['logo'], Auth::id(), [
            'w' => 256,
            'h' => 256,
            'name' => $thumbnailName,
        ]);

        $event = $this->eventRepository->update(
            [
                'logo_img_path' => $logoImageFilePath,
            ],
            $id,
        );

        CacheServiceFacade::forget(CacheKeys::eventSessionsByEventIdKey($event['id']));
        CacheServiceFacade::forget(CacheKeys::eventByEventSessionIdKey($event['id']));
        return [
            'logo_img_path' => $logoImageFilePath,
            'logo_tmb_img_path' => $logoThumbnailImageFilePath,
        ];
    }

    public function deleteCoverImg($id)
    {
        $filePath = $this->eventRepository->findByIdForCurrentAuthedUser($id)->cover_img_path;
        if (!$filePath) {
            return true;
        }
        $file = str_replace('/storage/', '', $filePath);
        $fileTmb = dirname($file) . '/tmb_' . basename($filePath);

        File::delete(storage_path('app/public/' . $file));
        File::delete(storage_path('app/public/' . $fileTmb));

        $event = $this->eventRepository->update(
            [
                'cover_img_path' => null,
            ],
            $id,
        );

        CacheServiceFacade::forget(CacheKeys::eventSessionsByEventIdKey($event['id']));
        CacheServiceFacade::forget(CacheKeys::eventByEventSessionIdKey($event['id']));
        return [
            'cover_img_path' => ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER,
            'cover_tmb_img_path' => ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER,
        ];
    }

    public function deleteLogoImg($id)
    {
        $filePath = $this->eventRepository->findByIdForCurrentAuthedUser($id)->logo_img_path;

        $file = str_replace('/storage/', '', $filePath);
        $fileTmb = dirname($file) . '/tmb_' . basename($filePath);

        File::delete(storage_path('app/public/' . $file));
        File::delete(storage_path('app/public/' . $fileTmb));

        $event = $this->eventRepository->update(
            [
                'logo_img_path' => null,
            ],
            $id,
        );

        CacheServiceFacade::forget(CacheKeys::eventSessionsByEventIdKey($event['id']));
        CacheServiceFacade::forget(CacheKeys::eventByEventSessionIdKey($event['id']));
        return [
            'logo_img_path' => ImagePlaceholders::LOGO_PLACEHOLDER,
            'logo_tmb_img_path' => ImagePlaceholders::LOGO_PLACEHOLDER,
        ];
    }

    public function redirect($eventId, $redirectUrl)
    {
        $eventSessions = $this->eventSessionRepository->findByEventId($eventId);

        $data = new BaseJsonResource(
            scope: WebSocketScopes::EVENT,
            mutation: WebSocketMutations::SOCK_REDIRECT_USERS,
            data: [
                'url' => $redirectUrl,
            ],
        );

        $channels = [];
        foreach ($eventSessions as $eventSession) {
            $channels[] = $eventSession->channel;
            $channels[] = $eventSession->private_channel;
        }

        return $this->webSocketService->publish($channels, $data);
    }

    public function reception($id)
    {
        $event = $this->eventRepository->findByIdForReception($id);

        $eventSessions = [];
        $eventData = [];
        foreach ($event as $eventItem) {
            if (!isset($eventSessions[$eventItem['event_session_id']])) {
                $eventSessions[$eventItem['event_session_id']] = [
                    'id' => $eventItem['event_session_id'],
                    'code' => $eventItem['event_session_code'],
                    'name' => $eventItem['event_session_name'],
                    'fare_id' => $eventItem['event_session_fare_id'],
                    'logo_img_path' => $eventItem['event_session_logo_img_path'] ?? ImagePlaceholders::LOGO_PLACEHOLDER,
                    'channel' => $eventItem['event_session_channel'],
                    'stream' => [
                        'id' => $eventItem['stream_id'],
                        'title' => $eventItem['stream_title'],
                        'cover_img_path' => $eventItem['stream_cover_img_path'] ?? ($eventItem['cover_img_path'] ?? ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER),
                        'start_at' => $eventItem['stream_start_at'],
                        'onair_at' => $eventItem['stream_onair_at'],
                        'is_onair' => $eventItem['stream_is_onair'],
                    ],
                ];
            }
            /*if (!isset($eventDataCollections[$eventItem['event_data_collection_template_id']])) {
                $eventDataCollections[$eventItem['event_data_collection_template_id']] = [
                    'id' => $eventItem['event_data_collection_template_id'],
                    'name' => $eventItem['event_data_collection_template_name'],
                    'label' => $eventItem['event_data_collection_template_label'],
                    'is_required' => $eventItem['event_data_collection_template_is_required'],
                    'is_editable' => $eventItem['event_data_collection_template_is_editable'],
                ];
            }*/
        }

        $eventData['event'] = $event[0];
        //$eventData['event_data_collection_templates'] = $eventDataCollections;
        $eventData['event_sessions'] = $eventSessions;

        return $eventData;
    }

    public function enter($requestData, $eventId)
    {
        $event = $this->eventRepository->findById($eventId);

        $this->checkEventDataCollection($event['is_data_collection_enabled'], $requestData, $eventId);

        $multiTicket = $this->eventTicketService->checkTicket($event['is_multi_ticket_enabled'], $requestData, $event);
        $uniqueTicket = $this->eventTicketService->checkTicket($event['is_unique_ticket_enabled'], $requestData, $event, false);

        if ($event['is_multi_ticket_enabled'] && $event['is_unique_ticket_enabled']) {
            if (!$multiTicket && !$uniqueTicket) {
                throw new ValidationException(__('Validation error: Ticket is not found or is not valid!'));
            }
            $this->eventAccessService->attachRoleToEventByUser(Auth::user(), $event, Roles::MEMBER);
        } elseif (!$multiTicket || !$uniqueTicket) {
            throw new ValidationException(__('Validation error: Ticket is not found or is not valid!'));
        } elseif (!$event['is_multi_ticket_enabled'] && !$event['is_unique_ticket_enabled']) {
            $this->eventAccessService->attachRoleToEventByUser(Auth::user(), $event, Roles::MEMBER);
        }

        $roles = $this->eventAccessService->getCurrentUserRolesToEvent($eventId);

        return $roles;
    }

    public function checkEventDataCollection($isCheckEnabled, $requestData, $eventId)
    {
        if ($isCheckEnabled) {
            $eventDataCollectionTemplates = $this->eventDataCollectionTemplateRepository->getTemplatesByEventId($eventId);

            if(!$eventDataCollectionTemplates || !count($eventDataCollectionTemplates)) return;

            if (!isset($requestData['data_collection']) && empty($requestData['data_collection'])) {
                throw new ValidationException(__('Validation error: Data collection fields is required!'));
            }

            $requestDataCollection = [];
            foreach ($requestData['data_collection'] as $requestDataCollectionItem) {
                $requestDataCollection[$requestDataCollectionItem['event_data_collection_template_id']] = $requestDataCollectionItem;
            }

            if ($eventDataCollectionTemplates) {
                $userFields = [];
                foreach ($eventDataCollectionTemplates as $eventDataCollectionTemplate) {
                    if ($eventDataCollectionTemplate['is_required'] && (!isset($requestDataCollection[$eventDataCollectionTemplate['id']]) || (isset($requestDataCollection[$eventDataCollectionTemplate['id']]) && (!isset($requestDataCollection[$eventDataCollectionTemplate['id']]['value']) || (isset($requestDataCollection[$eventDataCollectionTemplate['id']]['value']) && empty($requestDataCollection[$eventDataCollectionTemplate['id']]['value'])))))) {
                        throw new ValidationException(__('Validation error: Required data collection fields is not provided!'));
                    }

                    if (isset($requestDataCollection[$eventDataCollectionTemplate['id']]) && isset($requestDataCollection[$eventDataCollectionTemplate['id']]['value']) && !empty($requestDataCollection[$eventDataCollectionTemplate['id']]['value']) && isset($requestDataCollection[$eventDataCollectionTemplate['id']]['event_data_collection_template_id']) && !empty($requestDataCollection[$eventDataCollectionTemplate['id']]['event_data_collection_template_id'])) {
                        $this->eventDataCollectionRepository->createOrUpdate(
                            [
                                'value' => $requestDataCollection[$eventDataCollectionTemplate['id']]['value'],
                            ],
                            [
                                'event_id' => $eventId,
                                'user_id' => Auth::id(),
                                'event_data_collection_template_id' => $requestDataCollection[$eventDataCollectionTemplate['id']]['event_data_collection_template_id'],
                            ],
                        );

                        $userFields[$eventDataCollectionTemplate['name']] = $requestDataCollection[$eventDataCollectionTemplate['id']]['value'];
                    }
                }

                if (!empty($userFields)) {
                    $this->userRepository->update($userFields, Auth::id());
                }
            }
        }
    }

    public function getAllForAdminEventSessionsList(){
        return  $this->eventSessionRepository->allForAdmin();
    }

    public function getAllForAdminEventsList(array $filter)
    {
        return  $this->eventRepository->allForAdminEvents($filter);
    }

    public function getForAdminEventById($event_id){
        $event = $this->eventRepository->findById($event_id);
        $event->sessions = $this->eventSessionRepository->findAllByEventId($event_id);
        $event->user = $this->userRepository->findByProjectId($event->project_id);

        return $event;
    }
}
