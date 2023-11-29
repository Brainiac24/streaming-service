<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\ValidationException;
use App\Http\Requests\ContactGroup\CreateContactGroupRequest;
use App\Http\Requests\ContactGroup\UpdateContactGroupRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\ContactGroup\ContactGroupRepository;
use Request;
use Response;

class ContactGroupController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ContactGroupController:list',
        'findById' => 'ContactGroupController:findById',
        'create' => 'ContactGroupController:create',
        'update' => 'ContactGroupController:update',
        'delete' => 'ContactGroupController:delete',
    ];

    public function __construct(public ContactGroupRepository $contactGroupRepository)
    {
        //
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->contactGroupRepository->findByIdForCurrentAuthedUser($id))
        );
    }

    public function findByEventId($eventId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->contactGroupRepository->getByEventIdForCurrentAuthedUser($eventId))
        );
    }

    public function create(CreateContactGroupRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->contactGroupRepository->create($request->validated()))
        );
    }

    public function update(UpdateContactGroupRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->contactGroupRepository->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        if (!Request::get('confirmed', false)) {
            throw new ValidationException('Validation error: Please confirm the action!');
        }
        $this->contactGroupRepository->delete($id);

        return Response::apiSuccess();
    }
}
