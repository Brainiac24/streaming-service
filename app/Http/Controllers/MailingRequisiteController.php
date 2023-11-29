<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailingRequisite\CreateMailingRequisiteRequest;
use App\Http\Requests\MailingRequisite\UpdateMailingRequisiteRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\MailingRequisite\MailingRequisiteRepository;
use Response;

class MailingRequisiteController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'MailingRequisiteController:list',
        'findById' => 'MailingRequisiteController:findById',
        'create' => 'MailingRequisiteController:create',
        'update' => 'MailingRequisiteController:update',
        'delete' => 'MailingRequisiteController:delete',
    ];

    public function __construct(public MailingRequisiteRepository $mailingRequisiteRepository)
    {
        //
    }

    public function list($projectId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingRequisiteRepository->list($projectId))
        );
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingRequisiteRepository->findById($id))
        );
    }

    public function create(CreateMailingRequisiteRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingRequisiteRepository->create($request->validated()))
        );
    }

    public function update(UpdateMailingRequisiteRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingRequisiteRepository->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        $this->mailingRequisiteRepository->delete($id);

        return Response::apiSuccess();
    }
}
