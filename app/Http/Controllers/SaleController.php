<?php

namespace App\Http\Controllers;

use App\Constants\CacheKeys;
use App\Constants\Roles;
use App\Constants\SaleStatuses;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Http\Requests\Sale\CreateSaleRequest;
use App\Http\Requests\Sale\UpdateSaleRequest;
use App\Http\Requests\Sale\UpdateSaleSortRequest;
use App\Http\Resources\BaseJsonResource;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Sale\SaleService;
use App\Services\WebSocket\WebSocketService;
use Auth;
use DB;
use Response;

class SaleController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'SaleController:list',
        'findById' => 'SaleController:findById',
        'create' => 'SaleController:create',
        'update' => 'SaleController:update',
        'delete' => 'SaleController:delete'
    ];

    public function __construct(public SaleService $saleService, public WebSocketService $webSocketService)
    {
        //
    }

    public function list($eventSessionId)
    {
        return Response::apiSuccess(
            new BaseJsonResource(data: $this->saleService->list($eventSessionId))
        );
    }


    public function findById($id)
    {
        //
    }


    public function create(CreateSaleRequest $request)
    {

        DB::beginTransaction();
        try {
            $requestData = $request->validated();

            $data = $this->saleService->create($requestData);

            CacheServiceFacade::tags(CacheKeys::eventSessionIdTag($data['event_session_id']))
                ->flush();

            $socketData = new BaseJsonResource(
                data: [
                    'sale' => $data
                ],
                mutation: WebSocketMutations::SOCK_NEW_SALE,
                scope: WebSocketScopes::EVENT
            );

            $this->webSocketService->publishByEventSessionId($socketData, $data['event_session_id'], true);

            if (isset($requestData['is_publish']) && $requestData['is_publish']) {
                $this->share($data['id']);
            }

            $result = Response::apiSuccess(new BaseJsonResource(data: $data));
            if (DB::getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (DB::getPdo()->inTransaction()) {
                DB::rollBack();
            }

            throw $th;
        }
        return $result;
    }


    public function update(UpdateSaleRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $requestData = $request->validated();

            $data = $this->saleService->update($requestData, $id);

            CacheServiceFacade::tags(CacheKeys::saleIdTag($id))
                ->flush();

            $socketData = new BaseJsonResource(
                data: [
                    'sale' => $data
                ],
                mutation: WebSocketMutations::SOCK_EDIT_SALE,
                scope: WebSocketScopes::EVENT
            );

            $isOnlyPrivateChannel = true;
            if ($data['sale_status_id'] == SaleStatuses::SHARED) {
                $isOnlyPrivateChannel = false;
            }

            $this->webSocketService->publishByEventSessionId($socketData, $data['event_session_id'], $isOnlyPrivateChannel);

            if (isset($requestData['is_publish']) && $requestData['is_publish']) {
                $this->share($data['id']);
            }

            $result = Response::apiSuccess(new BaseJsonResource(data: $data));
            if (DB::getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (DB::getPdo()->inTransaction()) {
                DB::rollBack();
            }

            throw $th;
        }
        return $result;
    }

    public function sort(UpdateSaleSortRequest $request)
    {
        $data = $this->saleService->updateSort($request->validated());

        $saleIds = [];
        foreach ($data['result'] as $item) {
            $saleIds[] = CacheKeys::saleIdTag($item['id']);
        }

        CacheServiceFacade::tags($saleIds)
            ->flush();

        $socketData = new BaseJsonResource(
            data: [
                'sale' => $data['result']
            ],
            mutation: WebSocketMutations::SOCK_SORT_SALES,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByEventSessionId($socketData, $data['event_session_id']);

        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }


    public function delete($id)
    {
        $accessGroupId = $this->saleService->saleRepository->accessGroupIdBySaleId($id);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        CacheServiceFacade::forget(CacheKeys::accessGroupBySaleIdKey($id));

        $data = $this->saleService->saleRepository->findById($id)->toArray();

        $this->saleService->delete($id);

        CacheServiceFacade::tags(CacheKeys::saleIdTag($id))
            ->flush();

        $socketData = new BaseJsonResource(
            data: [
                'sale' => $data
            ],
            mutation: WebSocketMutations::SOCK_DELETE_SALE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByEventSessionId($socketData, $data['event_session_id'], true);

        return Response::apiSuccess(new BaseJsonResource());
    }

    public function share($id)
    {
        $data = $this->saleService->updateStatusToShared($id);

        CacheServiceFacade::tags(CacheKeys::saleIdTag($id))
            ->flush();

        $socketData = new BaseJsonResource(
            data: [
                'sale' => $data
            ],
            mutation: WebSocketMutations::SOCK_SHARE_SALE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByEventSessionId($socketData, $data['event_session_id']);

        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }

    public function done($id)
    {
        $data = $this->saleService->updateStatusToDone($id);

        CacheServiceFacade::tags(CacheKeys::saleIdTag($id))
            ->flush();

        $socketData = new BaseJsonResource(
            data: [
                'sale' => $data
            ],
            mutation: WebSocketMutations::SOCK_DONE_SALE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByEventSessionId($socketData, $data['event_session_id']);

        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }

    public function click($id)
    {
        $data = $this->saleService->updateClicksCount($id);

        $sale = $this->saleService->saleRepository->findById($id);

        CacheServiceFacade::tags(CacheKeys::saleIdTag($id))
            ->flush();

        $socketData = new BaseJsonResource(
            data: [
                'sale' => $data
            ],
            mutation: WebSocketMutations::SOCK_CLICK_SALE,
            scope: WebSocketScopes::EVENT
        );

        $this->webSocketService->publishByEventSessionId($socketData, $sale['event_session_id']);


        return Response::apiSuccess(new BaseJsonResource(data: $data));
    }
}
