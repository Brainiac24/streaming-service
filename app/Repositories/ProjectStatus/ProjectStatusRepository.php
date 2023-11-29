<?php

namespace App\Repositories\ProjectStatus;

use App\Models\ProjectStatus;
use App\Repositories\BaseRepository;

class ProjectStatusRepository extends BaseRepository
{
    public function __construct(public ProjectStatus $projectStatus)
    {
        parent::__construct($projectStatus);
    }
}
