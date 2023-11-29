<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventSessionVisit\StatsEventSessionVisitRequest;
use App\Http\Requests\EventSessionVisit\UserStatsEventSessionVisitRequest;
use App\Http\Requests\EventSessionVisit\ViewersTimelineEventSessionVisitRequest;
use App\Http\Resources\BaseJsonResource;
use App\Services\EventSessionVisit\EventSessionVisitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Response;

class EventSessionVisitController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventSessionVisitController:list',
        'findById' => 'EventSessionVisitController:findById',
        'create' => 'EventSessionVisitController:create',
        'update' => 'EventSessionVisitController:update',
        'delete' => 'EventSessionVisitController:delete'
    ];

    public function __construct(public EventSessionVisitService $eventSessionVisitService)
    {
        //
    }

    public function exportUserList(UserStatsEventSessionVisitRequest $request)
    {
        return $this->eventSessionVisitService->exportUserList($request->eventSessionId);
    }

    public function downloadUserList(UserStatsEventSessionVisitRequest $request){
        return $this->eventSessionVisitService->downloadUserList($request->eventSessionId);
    }

    public function list()
    {
        //
    }

    public function getStreamStats(StatsEventSessionVisitRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->eventSessionVisitService->getStreamStats($request->eventSessionId, $request->streamId))
        );
    }

    public function getViewersStats(ViewersTimelineEventSessionVisitRequest $request)
    {
        return $this->eventSessionVisitService->getViewersStats($request->streamId);
    }

    public function downloadViewersStats(ViewersTimelineEventSessionVisitRequest $request)
    {
        return $this->eventSessionVisitService->downloadViewersStats($request->streamId);
    }

    public function create($id)
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function delete($id)
    {
        //
    }
}
