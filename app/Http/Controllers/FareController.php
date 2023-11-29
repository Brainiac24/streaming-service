<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Http\Requests\Fare\CreateFareRequest;
use App\Http\Requests\Fare\UpdateFareRequest;
use App\Http\Resources\Fare\FareItemResource;
use App\Http\Resources\Fare\FareListResource;
use App\Http\Resources\Fare\UpgradeFareListResource;
use App\Repositories\Fare\FareRepository;
use App\Services\Cache\CacheServiceFacade;
use Illuminate\Support\Facades\Response;

class FareController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'FareController:list',
        'listWithUpgradePrices' => 'FareController:listWithUpgradePrices',
        'findById' => 'FareController:findById',
        'create' => 'FareController:create',
        'update' => 'FareController:update',
        'delete' => 'FareController:delete'
    ];

    public function __construct(public FareRepository $fareRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new FareListResource($this->fareRepository->allWithFareType())
        );
    }

    public function listWithUpgradePrices($id)
    {
        return Response::apiSuccess(
            new UpgradeFareListResource($this->fareRepository->allWithFareType(), $this->fareRepository->findById($id))
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new FareItemResource($this->fareRepository->findById($id))
        );
    }


    public function create(CreateFareRequest $request)
    {
        $fareRequestData = $request->validated();
        $fareRequestData['config_json'] = $fareRequestData['config'];
        unset($fareRequestData['config']);
        return Response::apiSuccess(
            new FareItemResource($this->fareRepository->create($fareRequestData))
        );
    }


    public function update(UpdateFareRequest $request, $id)
    {
        $fareRequestData = $request->validated();
        $fareRequestData['config_json'] = $fareRequestData['config'];
        unset($fareRequestData['config']);

        $fare = $this->fareRepository->update($fareRequestData, $id);

        CacheServiceFacade::tags(CacheKeys::fareIdTag($id))
            ->flush();

        return Response::apiSuccess(
            new FareItemResource($fare)
        );
    }


    public function delete($id)
    {
        $this->fareRepository->delete($id);

        CacheServiceFacade::tags(CacheKeys::fareIdTag($id))
            ->flush();

        return Response::apiSuccess();
    }
}
