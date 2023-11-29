<?php

namespace App\Http\Controllers;

use App\Constants\ProjectStatuses;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Event\EventItemSupportResource;
use App\Http\Resources\Event\EventListResource;
use App\Repositories\Project\ProjectRepository;
use App\Services\Event\EventService;
use App\Services\Project\ProjectService;
use DB;
use Illuminate\Support\Facades\Response;

class ProjectController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'ProjectController:list',
        'findById' => 'ProjectController:findById',
        'create' => 'ProjectController:create',
        'update' => 'ProjectController:update',
        'delete' => 'ProjectController:delete',
        'archiveList' => 'ProjectController:archiveList',
        'archive' => 'ProjectController:archive',
        'revert' => 'ProjectController:revert',
    ];

    public function __construct(
        public ProjectService $projectService,
        public ProjectRepository $projectRepository,
        public EventService $eventService
    ) {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectRepository->allByProjectStatusIdForCurrentAuthedUser(ProjectStatuses::ACTIVE))
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectRepository->findById($id))
        );
    }


    public function create(CreateProjectRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = Response::apiSuccess(
                new BaseJsonResource(data: $this->projectService->create($request->validated()))
            );
            if (DB::getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (DB::getPdo()->inTransaction()) {
                DB::rollBack();
            }

            throw $th;
        }
        return $result;
    }


    public function update(UpdateProjectRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $result = Response::apiSuccess(
                new BaseJsonResource(data: $this->projectService->update($request->validated(), $id))
            );
            if (DB::getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (DB::getPdo()->inTransaction()) {
                DB::rollBack();
            }

            throw $th;
        }
        return $result;
    }


    public function delete($id)
    {
        $this->projectService->delete($id);

        return Response::apiSuccess();
    }

    public function archiveList()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectService->archiveList())
        );
    }

    public function archive($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectService->archive($id))
        );
    }

    public function revert($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->projectService->revert($id))
        );
    }

    public function getEventsByProjectId($id)
    {
        return Response::apiSuccess(
            new EventListResource($this->eventService->eventRepository->findByProjectIdForCurrentAuthedUser($id))
        );
    }

    public function support($link)
    {
        return Response::apiSuccess(
            new EventItemSupportResource($this->projectService->support($link))
        );
    }
}
