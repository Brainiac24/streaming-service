<?php

namespace App\Services\Project;

use App\Constants\ProjectStatuses;
use App\Repositories\Project\ProjectRepository;
use App\Services\Image\ImageService;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    public function __construct(public ProjectRepository $projectRepository, public ImageService $imageService)
    {
    }

    public function create($data)
    {
        if (isset($data['cover']) && !empty($data['cover'])) {
            $data['cover_img_path'] = $this->imageService->storeFromBase64($data['cover'], Auth::id());
            unset($data['cover']);
        }

        $data['user_id'] = Auth::id();
        $data['project_status_id'] = ProjectStatuses::ACTIVE;

        return $this->projectRepository->create($data);
    }

    public function update($data, $id)
    {

        if (isset($data['cover']) && !empty($data['cover'])) {
            $data['cover_img_path'] = $this->imageService->storeFromBase64($data['cover'], Auth::id());
            unset($data['cover']);
        }

        return $this->projectRepository->updateProjectForCurrentAuthedUser($data, $id);
    }

    public function delete($id)
    {
        return $this->projectRepository->deleteProjectForCurrentAuthedUser($id);
    }

    public function archive($id)
    {
        return $this->projectRepository->updateStatusForCurrentAuthedUser($id, ProjectStatuses::ARCHIVED);
    }

    public function revert($id)
    {
        return $this->projectRepository->updateStatusForCurrentAuthedUser($id, ProjectStatuses::ACTIVE);
    }

    public function archiveList()
    {
        return $this->projectRepository->allByProjectStatusIdForCurrentAuthedUser(ProjectStatuses::ARCHIVED);
    }

    public function support($link)
    {
        return $this->projectRepository->findByLinkForSupport($link);
    }
}
