<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessGroup\CreateAccessGroupRequest;
use App\Http\Requests\AccessGroup\UpdateAccessGroupRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\AccessGroup\AccessGroupRepository;
use Illuminate\Support\Facades\Response;

class AccessGroupController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'AccessGroupController:list',
        'create' => 'AccessGroupController:create',
        'update' => 'AccessGroupController:update',
        'delete' => 'AccessGroupController:delete'
    ];

    public function __construct(private AccessGroupRepository $accessGroupRepository)
    {
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->accessGroupRepository->allWithPagination())
        );
    }

    public function create(CreateAccessGroupRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->accessGroupRepository->create($request->validated()))
        );
    }

    public function update(UpdateAccessGroupRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->accessGroupRepository->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        $this->accessGroupRepository->delete($id);

        return Response::apiSuccess();
    }
}
