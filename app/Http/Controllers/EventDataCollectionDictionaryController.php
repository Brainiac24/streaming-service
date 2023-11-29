<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventDataCollectionDictionary\CreateEventDataCollectionDictionaryRequest;
use App\Http\Requests\EventDataCollectionDictionary\UpdateEventDataCollectionDictionaryRequest;
use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\EventDataCollectionDictionary\EventDataCollectionDictionaryItemResource;
use App\Http\Resources\EventDataCollectionDictionary\EventDataCollectionDictionaryListResource;
use App\Repositories\EventDataCollectionDictionary\EventDataCollectionDictionaryRepository;
use Illuminate\Support\Facades\Response;

class EventDataCollectionDictionaryController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'EventDataCollectionDictionaryController:list',
        'findById' => 'EventDataCollectionDictionaryController:findById',
        'create' => 'EventDataCollectionDictionaryController:create',
        'update' => 'EventDataCollectionDictionaryController:update',
        'delete' => 'EventDataCollectionDictionaryController:delete'
    ];

    public function __construct(private EventDataCollectionDictionaryRepository $eventDataCollectionDictionaryRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new EventDataCollectionDictionaryListResource($this->eventDataCollectionDictionaryRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new EventDataCollectionDictionaryItemResource($this->eventDataCollectionDictionaryRepository->findById($id))
        );
    }


    public function create(CreateEventDataCollectionDictionaryRequest $request)
    {
        return Response::apiSuccess(
            new EventDataCollectionDictionaryItemResource($this->eventDataCollectionDictionaryRepository->create($request->validated()))
        );
    }


    public function update(UpdateEventDataCollectionDictionaryRequest $request, $id)
    {
        return Response::apiSuccess(
            new EventDataCollectionDictionaryItemResource($this->eventDataCollectionDictionaryRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->eventDataCollectionDictionaryRepository->delete($id);

        return Response::apiSuccess();
    }
}
