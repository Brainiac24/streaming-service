<?php

namespace App\Repositories\Project;

use App\Models\Project;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Str;

class ProjectRepository extends BaseRepository
{
    public function __construct(public Project $project)
    {
        parent::__construct($project);
    }

    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->project->currentAuthedUser()->findOrFail($id);
    }

    public function updateProjectForCurrentAuthedUser(array $data, $id)
    {
        $project = $this->project->currentAuthedUser()->findOrFail($id);
        $project->update($data);
        return $project;
    }


    public function deleteProjectForCurrentAuthedUser($id)
    {
        if ($this->project->currentAuthedUser()->where('projects.id', $id)->delete() == 0) {
            throw new ModelNotFoundException();
        }
        return true;
    }

    public function updateStatusForCurrentAuthedUser($id, $statusId)
    {
        $project = $this->project->currentAuthedUser()->findOrFail($id);
        $project->project_status_id = $statusId;
        $project->link=$project->link.'_'.Str::lower(Str::random(16));
        $project->save();
        return $project;
    }

    public function allByProjectStatusIdForCurrentAuthedUser($projectStatusId)
    {
        return $this->project->currentAuthedUser()->where('project_status_id', $projectStatusId)->get();
    }
    public function allByUserId($user_id)
    {
        return $this->project
            ->join('project_statuses', 'project_statuses.id', 'projects.project_status_id')
            ->whereUserId($user_id)
            ->get(['projects.*', 'project_statuses.name as status_name']);
    }

    public function findByLinkForSupport($link)
    {
        return $this->project
            ->join('events', function ($join) use ($link) {
                $join
                    ->on('projects.id', '=', 'events.project_id')
                    ->where('events.link', $link);
            })
            ->first([
                'projects.support_name',
                'projects.support_link',
                'projects.support_phone',
                'projects.support_email',
                'projects.support_site',
            ]);
    }
}
