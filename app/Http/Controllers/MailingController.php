<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mailing\CreateMailingRequest;
use App\Http\Requests\Mailing\UpdateMailingFromCallbackRequest;
use App\Http\Requests\Mailing\UpdateMailingRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Mailing\MailingListResource;
use App\Repositories\Mailing\MailingRepository;
use App\Services\Mailing\MailingService;
use Illuminate\Http\JsonResponse;
use Response;

class MailingController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'MailingController:list',
        'findById' => 'MailingController:findById',
        'create' => 'MailingController:create',
        'update' => 'MailingController:update',
        'delete' => 'MailingController:delete',
    ];

    public function __construct(public MailingService $mailingService,public MailingRepository $mailingRepository)
    {
        //
    }

    public function list($eventId): JsonResponse
    {
        return Response::apiSuccess(
            new MailingListResource(mailings: $this->mailingRepository->allByEventId($eventId))
        );
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingRepository->findByIdForCurrentAuthedUser($id))
        );
    }

    public function create(CreateMailingRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingService->create($request->validated()))
        );
    }

    public function update(UpdateMailingRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingService->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        $this->mailingRepository->delete($id);

        return Response::apiSuccess();
    }

    public function updateCallback(UpdateMailingFromCallbackRequest $request, $uuid)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->mailingService->updateCallback($request->validated(), $uuid))
        );
    }
}
