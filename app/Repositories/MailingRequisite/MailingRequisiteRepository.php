<?php

namespace App\Repositories\MailingRequisite;

use App\Models\MailingRequisite;
use App\Repositories\BaseRepository;

class MailingRequisiteRepository extends BaseRepository
{
    public function __construct(public MailingRequisite $mailingRequisite)
    {
        parent::__construct($mailingRequisite);
    }

    public function list($projectId)
    {
        return $this->mailingRequisite
            ->currentAuthedUser()
            ->where('project_id', $projectId)
            ->get(['mailing_requisites.*']);
    }

}
