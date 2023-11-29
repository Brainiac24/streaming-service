<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStatus\CreateProjectStatusRequest;
use App\Http\Requests\ProjectStatus\UpdateProjectStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\ProjectStatus\ProjectStatusRepository;
use Illuminate\Support\Facades\Response;

class ProjectStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ProjectStatusController:list',
        'findById' => 'ProjectStatusController:findById',
        'create' => 'ProjectStatusController:create',
        'update' => 'ProjectStatusController:update',
        'delete' => 'ProjectStatusController:delete'
    ];

    public function __construct(private ProjectStatusRepository $projectStatusRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectStatusRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectStatusRepository->findById($id))
        );
    }


    public function create(CreateProjectStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectStatusRepository->create($request->validated()))
        );
    }


    public function update(UpdateProjectStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectStatusRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->projectStatusRepository->delete($id);

        return Response::apiSuccess();
    }
}
