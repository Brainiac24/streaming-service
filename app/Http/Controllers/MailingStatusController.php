<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailingStatus\CreateMailingStatusRequest;
use App\Http\Requests\MailingStatus\UpdateMailingStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\MailingStatus\MailingStatusRepository;
use Response;

class MailingStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'MailingStatusController:list',
        'findById' => 'MailingStatusController:findById',
        'create' => 'MailingStatusController:create',
        'update' => 'MailingStatusController:update',
        'delete' => 'MailingStatusController:delete',
    ];

    public function __construct(public MailingStatusRepository $mailingStatusRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingStatusRepository->allWithPagination())
        );
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingStatusRepository->findById($id))
        );
    }

    public function create(CreateMailingStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingStatusRepository->create($request->validated()))
        );
    }

    public function update(UpdateMailingStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingStatusRepository->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        $this->mailingStatusRepository->delete($id);

        return Response::apiSuccess();
    }
}
