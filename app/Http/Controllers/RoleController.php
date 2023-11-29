<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Response;
use App\Repositories\Role\RoleRepository;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;

class RoleController extends Controller
{
    static $actionPermissionMap = [
        'list' => 'RoleController:list',
        'findById' => 'RoleController:findById',
        'create' => 'RoleController:create',
        'update' => 'RoleController:update',
        'delete' => 'RoleController:delete'
    ];

    public function __construct(private RoleRepository $roleRepository)
    {
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->roleRepository->allWithPagination())
        );
    }

    public function create(CreateRoleRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->roleRepository->create($request->validated()))
        );
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data:$this->roleRepository->update($request->validated(), $id))
        );

    }

    public function delete($id)
    {
        $this->roleRepository->delete($id);

        return Response::apiSuccess();
    }
}
