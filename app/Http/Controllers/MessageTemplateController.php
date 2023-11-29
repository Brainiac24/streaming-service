<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageTemplate\CreateMessageTemplateRequest;
use App\Http\Requests\MessageTemplate\UpdateMessageTemplateRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\MessageTemplate\MessageTemplateRepository;
use Response;

class MessageTemplateController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'MessageTemplateController:list',
        'findById' => 'MessageTemplateController:findById',
        'create' => 'MessageTemplateController:create',
        'update' => 'MessageTemplateController:update',
        'delete' => 'MessageTemplateController:delete',
    ];

    public function __construct(public MessageTemplateRepository $messageTemplateRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->messageTemplateRepository->allWithPagination())
        );
    }

    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->messageTemplateRepository->findById($id))
        );
    }

    public function create(CreateMessageTemplateRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->messageTemplateRepository->create($request->validated()))
        );
    }

    public function update(UpdateMessageTemplateRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->messageTemplateRepository->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        $this->messageTemplateRepository->delete($id);

        return Response::apiSuccess();
    }
}
