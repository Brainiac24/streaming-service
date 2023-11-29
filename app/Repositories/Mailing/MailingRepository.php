<?php

namespace App\Repositories\Mailing;

use App\Models\Mailing;
use App\Repositories\BaseRepository;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Request;

class MailingRepository extends BaseRepository
{
    public function __construct(public Mailing $mailing)
    {
        parent::__construct($mailing);
    }

    public function findByJobUuId($uuid)
    {
        return $this->mailing->where('job_uuid' , $uuid)->first();
    }

    public function allWithPagination(array | string $columns = ['*'], string $pageName = "page", int | null $page = null, Closure | int | null $perPage = 15)
    {
        $perPage = (int) Request::get('perPage', $perPage);
        return $this->mailing->currentAuthedUserByAuthedId()->withQueryFilters()->paginate($perPage, $columns, $pageName, $page);
    }

    public function allByEventId($eventId): Collection
    {
        return $this->mailing
            ->join('mailing_statuses',  'mailing_statuses.id', '=', 'mailings.mailing_status_id')
            ->leftJoin('event_sessions',  'event_sessions.id', '=', 'mailings.event_session_id')
            ->leftJoin('mailing_requisites',  'mailing_requisites.id', '=', 'mailings.mailing_requisite_id')
            ->leftJoin('contact_groups',  'contact_groups.id', '=', 'mailings.contact_group_id')
            ->currentAuthedUserByAuthedId()
            ->where('mailings.event_id', $eventId)
            ->get([
                'mailings.*',
                'mailing_statuses.name as mailing_status_name',
                'event_sessions.name as event_session_name',
                'contact_groups.name as contact_group_name',
                'mailing_requisites.host as requisites_host'
            ]);
    }

    public function findByIdForCurrentAuthedUser($id)
    {
        return $this->model->currentAuthedUserByAuthedId()->findOrFail($id);
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function delete($id)
    {
        $mailing = $this->model->currentAuthedUserByAuthedId()->where('id', $id)->first();

        if ($mailing->delete() == 0) {
            throw new ModelNotFoundException();
        }
        return true;
    }
}
