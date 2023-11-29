<?php

namespace App\Http\Controllers;

use App\Http\Requests\Fare\CreateFareRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\Fare\FareRepository;
use Illuminate\Http\Request;
use Response;

class FareTypeController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'FareTypeController:list',
        'findById' => 'FareTypeController:findById',
        'create' => 'FareTypeController:create',
        'update' => 'FareTypeController:update',
        'delete' => 'FareTypeController:delete'
    ];

    public function __construct(public FareRepository $fareRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource($this->fareRepository->all())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource($this->fareRepository->findById($id))
        );
    }


    public function create(CreateFareRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource($this->fareRepository->create($request->validated()))
        );
    }


    public function update(Request $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource($this->fareRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->fareRepository->delete($id);

        return Response::apiSuccess();
    }
}
