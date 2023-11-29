<?php

namespace App\Services\Contact;

use App\Constants\CacheKeys;
use App\Constants\ContactGroups;
use App\Constants\EventTicketStatuses;
use App\Constants\EventTicketTypes;
use App\Constants\Roles;
use App\Constants\StatusCodes;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Resources\BaseJsonResource;
use App\Jobs\ContactExportJob;
use App\Repositories\ContactGroup\ContactGroupRepository;
use App\Repositories\Contact\ContactRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventTicket\EventTicketRepository;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\EventAccess\EventAccessService;
use App\Services\EventTicket\EventTicketService;
use App\Services\Helper\XlsxExportHelperService;
use Cache;
use Response;
use Storage;
use Validator;

class ContactService
{
    public function __construct(
        public ContactGroupRepository $contactGroupRepository,
        public ContactRepository $contactRepository,
        public XlsxExportHelperService $xlsxExportHelperService,
        public EventTicketService $eventTicketService,
        public EventTicketRepository $eventTicketRepository,
        public UserRepository $userRepository,
        public EventAccessService $eventAccessService,
        public EventRepository $eventRepository
    ) {
    }

    public function getContactListForExport($eventId, $requestData)
    {
        if (!$this->contactRepository->findByEventIdForCurrentAuthedUser($eventId)) {
            throw new NotFoundException();
        }

        $contactListFile = Cache::get(CacheKeys::contactListByEventIdKey($eventId));
        $link = url('api/v1/contacts/export/event/' . $eventId);
        if (isset($requestData['contact_group_id']) && !empty($requestData['contact_group_id'])) {
            $contactListFile = Cache::get(CacheKeys::contactListByEventIdAndContactGroupIdKey($eventId,$requestData['contact_group_id']));
            $link = url('api/v1/contacts/export/event/' . $eventId.'?contact_group_id='.$requestData['contact_group_id']);
        }
        if (!$contactListFile) {
            ContactExportJob::dispatch($eventId, $requestData);
            return Response::apiSuccess(
                new BaseJsonResource(
                    code: StatusCodes::IN_PROCESS,
                    message: __('In process'),
                )
            );
        }

        return Response::apiSuccess(
            new BaseJsonResource(
                code: StatusCodes::SUCCESS,
                message: __('Success'),
                data: [
                    'link' => $link
                ]
            )
        );
    }

    public function downloadContactList($eventId,$requestData){

        if (!$this->contactRepository->findByEventIdForCurrentAuthedUser($eventId)) {
            throw new NotFoundException();
        }

        $contactListFile = Cache::get(CacheKeys::contactListByEventIdKey($eventId));
        if (isset($requestData['contact_group_id']) && !empty($requestData['contact_group_id'])) {
            $contactListFile = Cache::get(CacheKeys::contactListByEventIdAndContactGroupIdKey($eventId,$requestData['contact_group_id']));
        }
        if (!$contactListFile) {
            return Response::apiError(
                new BaseJsonResource(
                    code: StatusCodes::NOT_FOUND_ERROR,
                    message: __('Not found!')
                ),
                403
            );
        }
        return Storage::disk("local")->download('local/xlsx/' . $contactListFile, headers: [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Length' => null,
        ]);
    }
    public function importFromFile($contactRequestData, $eventId)
    {
        $contactGroupEmailList = [];
        if (!isset($contactRequestData['contact_group_id'])) {

            $contactGroup = $this->contactGroupRepository->findByEventIdAndIsCommonTrueForCurrentAuthedUser($eventId);

            if (!$contactGroup) {
                $contactGroup = $this->contactGroupRepository->create([
                    'name' => ContactGroups::DEFAULT,
                    'event_id' => $eventId,
                ]);
            }

            $contactGroupId = $contactGroup['id'];
        } else {
            $contactGroupId = $contactRequestData['contact_group_id'];
            $contactGroupEmailList = $this->contactRepository->emailListByContactGroupId($contactGroupId)->pluck('email')->toArray();
        }
        //dd($contactGroupEmailList);
        $file = $contactRequestData['file'];
        $ext = $file->getClientOriginalExtension();
        if (!in_array($ext, ['xlsx', 'csv'])) {
            throw new ValidationException('Validation error: File extension must be xlsx or csv!');
        }

        if ($ext === 'xlsx') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        if ($ext === 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        }
        $spreadsheet = $reader->load($file->getPathName());

        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();

        $event = $this->eventRepository->findById($eventId);

        $ticketTypeId = null;
        $multiTicketId = null;
        $uniqTickets = null;
        if (isset($contactRequestData['event_ticket_type_id'])) {
            $ticketTypeId = $contactRequestData['event_ticket_type_id'];

            if ($ticketTypeId == EventTicketTypes::MULTI) {
                if (isset($contactRequestData['event_ticket_id'])) {
                    $multiTicketId = $contactRequestData['event_ticket_id'];
                } else {
                    $multiTicketId = $this->eventTicketService->generate($eventId, 1, EventTicketTypes::MULTI, true)[0]['id'];
                }
            } else if ($ticketTypeId == EventTicketTypes::UNIQUE) {
                $uniqTickets = $this->eventTicketService->generate($eventId, count($data) - 1, EventTicketTypes::UNIQUE, true);
            }
        }

        $columns = [];
        $numberOfRow = 1;
        $importedEmails = [];
        foreach ($data as $row) {
            $columnIndex = 0;
            $dataRow = [];
            $errorColumn = 0;
            $emailErrorValidate = false;
            foreach ($row as $key => $value) {
                if ($numberOfRow == 1 && !empty($value)) {
                    $columns[] = $value;
                } else if ($numberOfRow > 1) {
                    if (!empty($value)) {
                        if ($columns[$columnIndex] == 'email' || $columns[$columnIndex] == 'name' || $columns[$columnIndex] == 'lastname') {
                            $dataRow[$columns[$columnIndex]] = trim($value);
                            if ($columns[$columnIndex] == 'email') {
                                $dataRow[$columns[$columnIndex]] = strtolower($value);
                                $emailValidator = Validator::make($dataRow,['email'=>'email']);
                                if($emailValidator->fails())
                                {
                                    $emailErrorValidate = true;
                                    continue;
                                }
                            }
                        } else {
                            $dataRow['data_json'][$columns[$columnIndex]] = trim($value);
                        }
                    } else {
                        $errorColumn += 1;
                    }
                }
                $columnIndex++;
            }
            if($emailErrorValidate){
                continue;
            }

            if ($numberOfRow >= 2 && !($errorColumn >= count($columns)) && isset($dataRow['email'])) {
                $dataRow['contact_group_id'] = $contactGroupId;
                if ($ticketTypeId && $ticketTypeId == EventTicketTypes::MULTI) {
                    $dataRow['event_ticket_id'] = $multiTicketId;
                }
                if ($ticketTypeId && $ticketTypeId == EventTicketTypes::UNIQUE) {
                    $dataRow['event_ticket_id'] = $uniqTickets[$numberOfRow - 2]['id'];
                    $uniqTickets[$numberOfRow - 2]['event_ticket_status_id'] = EventTicketStatuses::RESERVED;
                    $uniqTickets[$numberOfRow - 2]->save();
                }
                $user = $this->userRepository->findByEmail($dataRow['email']);

                if ($user) {
                    $dataRow['user_id'] = $user['id'];
                    $this->setRoleAndTicket($event, $user, $dataRow);
                }
                if (!in_array($dataRow['email'], $contactGroupEmailList)) {
                    if (!in_array($dataRow['email'], $importedEmails)) {
                        $this->contactRepository->create($dataRow);
                        $importedEmails[] = $dataRow;
                    }
                }
            }
            if (!empty($value)) {
                $numberOfRow++;
            }
        };
        CacheServiceFacade::forget(CacheKeys::contactsWithEventIdKey());
    }

    public function create($requestData)
    {
        $requestData['email'] = strtolower(trim($requestData['email']));

        $contactGroupEmailList = $this->contactRepository->emailListByContactGroupId($requestData['contact_group_id'])->pluck('email')->toArray();

        if (in_array($requestData['email'], $contactGroupEmailList)) {
            throw new ValidationException('There are duplicate emails in Contact Group!', 0, null);
        }

        $user = $this->userRepository->findByEmail($requestData['email']);
        $contactGroup = $this->contactGroupRepository->findByIdForCurrentAuthedUser($requestData['contact_group_id']);

        $event = $this->eventRepository->findById($contactGroup['event_id']);

        if(isset($requestData['event_ticket_type_id']) && !empty($requestData['event_ticket_type_id'])){
            if (isset($requestData['event_ticket_id']) && !empty($requestData['event_ticket_id'])) {
                $this->checkTicketForActive($requestData['event_ticket_id']);
            } else {
                if ($requestData['event_ticket_type_id'] == EventTicketTypes::UNIQUE) {
                    $requestData['event_ticket_id'] = $this->eventTicketService->generate($event['id'], 1, EventTicketTypes::UNIQUE, true)[0]['id'];
                }
            }
        }


        if ($user) {
            $this->setRoleAndTicket($event, $user, $requestData);
            $requestData['user_id'] = $user['id'];
        }

        $contact = $this->contactRepository->create($requestData);



        if (isset($requestData['event_ticket_id']) && !empty($requestData['event_ticket_id'])) {
            $eventTicket = $this->eventTicketRepository->findById($requestData['event_ticket_id']);
            if ($eventTicket['event_ticket_type_id'] == EventTicketTypes::UNIQUE) {
                $this->eventTicketRepository->updateByModel(
                    [
                        'event_ticket_status_id' => EventTicketStatuses::RESERVED,
                    ],
                    $eventTicket
                );
            }
        }

        $contact = $this->contactRepository->updateByModel($requestData, $contact);

        CacheServiceFacade::forget(CacheKeys::contactsWithEventIdKey());

        return $contact;
    }


    public function allByEmail($email)
    {
        return $this->contactRepository->allWithEventId($email);
    }

    public function update($data, $id)
    {
        $contact = $this->contactRepository->findById($id);
        $oldTicketId = "";
        if (isset($data['event_ticket_id']) && !empty($data['event_ticket_id'])) {
            $this->checkTicketForActive($data['event_ticket_id']);
            $oldTicketId = $contact['event_ticket_id'];
        }

        $this->contactRepository->updateByModel(
            $data,
            $contact
        );

        if (isset($data['event_ticket_id']) && !empty($data['event_ticket_id'])) {
            $eventTicket = $this->eventTicketRepository->findById($data['event_ticket_id']);
            $oldEventTicket = $this->eventTicketRepository->findById($oldTicketId);
            if ($oldEventTicket && $oldEventTicket['event_ticket_type_id'] == EventTicketTypes::UNIQUE) {
                $this->eventTicketRepository->updateByModel(
                    ['event_ticket_status_id' => EventTicketStatuses::ACTIVE],
                    $oldEventTicket
                );
            }

            if ($eventTicket['event_ticket_type_id'] == EventTicketTypes::UNIQUE) {
                $this->eventTicketRepository->updateByModel(
                    ['event_ticket_status_id' => EventTicketStatuses::RESERVED],
                    $eventTicket
                );
            }
        }
    }

    public function checkTicketForActive($eventTicketId)
    {
        $eventTicket = $this->eventTicketRepository->findById($eventTicketId);

        if ($eventTicket['event_ticket_status_id'] != EventTicketStatuses::ACTIVE) {
            throw new ValidationException("Validation error: Ticket is not active or already used by another user!",);
        }

        return true;
    }

    public function setRoleAndTicket($event, $user, $requestData)
    {
        $requestData['user_id'] = $user['id'];

        if (!$event['is_unique_ticket_enabled'] && !$event['is_multi_ticket_enabled']) {
            $this->eventAccessService->attachRoleToEventByUser($user, $event, Roles::MEMBER);
        } else if (isset($requestData['event_ticket_id']) && !empty($requestData['event_ticket_id'])) {
            $eventTicket = $this->eventTicketRepository->findById($requestData['event_ticket_id']);
            $requestData['event_ticket_type_id'] = $eventTicket['event_ticket_type_id'];
            if ($eventTicket['event_ticket_status_id'] == EventTicketStatuses::ACTIVE) {
                $this->eventAccessService->attachRoleToEventByUser($user, $event, Roles::MEMBER);
                if ($eventTicket['event_ticket_type_id'] == EventTicketTypes::UNIQUE) {
                    $this->eventTicketRepository->updateByModel(
                        [
                            'event_ticket_status_id' => EventTicketStatuses::USED,
                            'user_id' => $user['id'],
                        ],
                        $eventTicket
                    );
                }
            }
        }
    }

    public function delete($id)
    {
        $contact = $this->contactRepository->findById($id);
        $this->contactRepository->delete($id);
        if ($contact['event_ticket_id']) {
            $eventTicket = $this->eventTicketRepository->findById($contact['event_ticket_id']);
            if ($eventTicket && $eventTicket['event_ticket_type_id'] == EventTicketTypes::UNIQUE) {
                $this->eventTicketRepository->updateByModel(
                    ['event_ticket_status_id' => EventTicketStatuses::ACTIVE],
                    $eventTicket
                );
            }
        }
        if ($contact['user_id']) {
            $contactGroup = $this->contactGroupRepository->findById($contact['contact_group_id']);
            $detachRequest['email'] = $contact['email'];
            $detachRequest['role_id'] = Roles::MEMBER;
            $this->eventAccessService->detachRoleToEvent($detachRequest, $contactGroup['event_id']);
        }
    }
}
