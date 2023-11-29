<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageType\CreateChatMessageTypeRequest;
use App\Http\Requests\ChatMessageType\UpdateChatMessageTypeRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\ChatMessageType\ChatMessageTypeRepository;
use Illuminate\Support\Facades\Response;

class ChatMessageTypeController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ChatMessageTypeController:list',
        'findById' => 'ChatMessageTypeController:findById',
        'create' => 'ChatMessageTypeController:create',
        'update' => 'ChatMessageTypeController:update',
        'delete' => 'ChatMessageTypeController:delete'
    ];

    public function __construct(private ChatMessageTypeRepository $chatMessageTypeRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->chatMessageTypeRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->chatMessageTypeRepository->findById($id))
        );
    }


    public function create(CreateChatMessageTypeRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->chatMessageTypeRepository->create($request->validated()))
        );
    }


    public function update(UpdateChatMessageTypeRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->chatMessageTypeRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->chatMessageTypeRepository->delete($id);

        return Response::apiSuccess();
    }
}
