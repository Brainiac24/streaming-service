<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Response;
use App\Repositories\Permission\PermissionRepository;
use App\Http\Requests\Permission\CreatePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;

class PermissionController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'PermissionController:list',
        'create' => 'PermissionController:create',
        'update' => 'PermissionController:update',
        'delete' => 'PermissionController:delete'
    ];

    public function __construct(private PermissionRepository $permissionRepository)
    {
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->permissionRepository->allWithPagination())
        );
    }

    public function create(CreatePermissionRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->permissionRepository->create($request->validated()))
        );
    }

    public function update(UpdatePermissionRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->permissionRepository->update($request->validated(), $id))
        );
    }

    public function delete($id)
    {
        $this->permissionRepository->delete($id);

        return Response::apiSuccess();
    }
}
