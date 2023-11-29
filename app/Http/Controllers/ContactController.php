<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Http\Requests\Contact\ContactExportRequest;
use App\Http\Requests\Contact\CreateContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Http\Requests\Contact\UploadContactsRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\Contact\ContactRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Contact\ContactService;
use DB;
use Response;

class ContactController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ContactController:list',
        'findById' => 'ContactController:findById',
        'create' => 'ContactController:create',
        'update' => 'ContactController:update',
        'delete' => 'ContactController:delete',
        'export' => 'ContactController:export'
    ];

    public function __construct(private ContactService $contactService, public ContactRepository $contactRepository)
    {
        //
    }

    public function findByContactGroupId($contactGroupId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->contactRepository->allByContactGroupId($contactGroupId))
        );
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->contactRepository->findByIdForCurrentAuthedUser($id))
        );
    }

    public function create(CreateContactRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = Response::apiSuccess(
                new BaseJsonResource(data: $this->contactService->create($request->validated()))
            );
            if (DB::getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (DB::getPdo()->inTransaction()) {
                DB::rollBack();
            }

            throw $th;
        }
        return $result;

    }

    public function update(UpdateContactRequest $request, $id)
    {
        CacheServiceFacade::forget(CacheKeys::contactsWithEventIdKey());

        return Response::apiSuccess(
            new BaseJsonResource(data: $this->contactService->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        $this->contactService->delete($id);

        CacheServiceFacade::forget(CacheKeys::contactsWithEventIdKey());

        return Response::apiSuccess();
    }

    public function uploadContactsFromFile(UploadContactsRequest $request, $eventId)
    {
        DB::beginTransaction();
        try {
            $this->contactService->importFromFile($request->validated(), $eventId);
            if (DB::getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (DB::getPdo()->inTransaction()) {
                DB::rollBack();
            }

            throw $th;
        }
        return Response::apiSuccess();
    }

    public function export(ContactExportRequest $request, $eventId)
    {
        return $this->contactService->getContactListForExport($eventId, $request->validated());
    }

    public function download(ContactExportRequest $request, $eventId)
    {
        return $this->contactService->downloadContactList($eventId, $request->validated());
    }
}
