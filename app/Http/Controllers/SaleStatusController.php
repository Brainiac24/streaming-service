<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleStatus\CreateSaleStatusRequest;
use App\Http\Requests\SaleStatus\UpdateSaleStatusRequest;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\SaleStatus\SaleStatusRepository;
use Illuminate\Support\Facades\Response;

class SaleStatusController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'SaleStatusController:list',
        'findById' => 'SaleStatusController:findById',
        'create' => 'SaleStatusController:create',
        'update' => 'SaleStatusController:update',
        'delete' => 'SaleStatusController:delete'
    ];

    public function __construct(private SaleStatusRepository $saleStatusRepository)
    {
        //
    }

    public function list()
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->saleStatusRepository->allWithPagination())
        );
    }


    public function findById($id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->saleStatusRepository->findById($id))
        );
    }


    public function create(CreateSaleStatusRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->saleStatusRepository->create($request->validated()))
        );
    }


    public function update(UpdateSaleStatusRequest $request, $id)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->saleStatusRepository->update($request->validated(), $id))
        );
    }


    public function delete($id)
    {
        $this->saleStatusRepository->delete($id);

        return Response::apiSuccess();
    }
}
