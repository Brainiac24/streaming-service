<?php

namespace App\Services\Sale;

use App\Constants\Roles;
use App\Constants\SaleStatuses;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Sale\SaleRepository;
use App\Repositories\SaleStat\SaleStatRepository;
use App\Services\Image\ImageService;
use Auth;

class SaleService
{
    public function __construct(
        public SaleRepository $saleRepository,
        public SaleStatRepository $saleStatRepository,
        public ImageService $imageService,
        public EventSessionRepository $eventSessionRepository
    ) {
    }

    public function list($eventSessionId)
    {
        return  $this->saleRepository->listByEventSessionId(eventSessionId: $eventSessionId)->toArray();
    }

    public function create($data)
    {
        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($data['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        if (isset($data['cover']) && !empty($data['cover'])) {
            $data['cover_img_path'] = $this->imageService->storeFromBase64($data['cover'], Auth::id());
            unset($data['cover']);
        }

        $data['sale_status_id'] = SaleStatuses::NEW;

        return $this->saleRepository->create($data);
    }

    public function update($data, $id)
    {
        $sale = $this->saleRepository->findById($id);

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($sale['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);


        if (isset($data['is_delete_cover']) && $data['is_delete_cover']) {
            $this->imageService->deleteByFilePath($sale['cover_img_path']);
        }

        if (isset($data['cover']) && !empty($data['cover'])) {
            $this->imageService->deleteByFilePath($sale['cover_img_path']);
            $data['cover_img_path'] = $this->imageService->storeFromBase64($data['cover'], Auth::id(), ['name_prefix' => 'sale_']);
            unset($data['cover']);
        }

        return $this->saleRepository->updateByModel($data, $sale);
    }

    public function updateSort($data)
    {
        $eventSessionId = null;

        foreach ($data['sales'] as $saleItem) {
            $sale = $this->saleRepository->findById($saleItem['id']);
            $eventSessionId = $sale['event_session_id'];

            $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($sale['event_session_id']);

            Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

            $result[] = $this->saleRepository->updateByModel($saleItem, $sale);
        }

        $result = [];
        if ($eventSessionId) {
            $result = $this->saleRepository->listByEventSessionId($eventSessionId);
        }

        return [
            'result' => $result,
            'event_session_id' => $eventSessionId,
        ];
    }

    public function delete($id)
    {
        $sale = $this->saleRepository->findById($id);

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($sale['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        return $this->saleRepository->deleteByModel($sale);
    }

    public function updateStatusToShared($id)
    {
        $sale = $this->saleRepository->findById($id);

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($sale['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);


        $data = [
            'sale_status_id' => SaleStatuses::SHARED,
        ];

        return $this->saleRepository->updateByModel($data, $sale);
    }

    public function updateStatusToDone($id)
    {
        $sale = $this->saleRepository->findById($id);

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($sale['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $data = [
            'sale_status_id' => SaleStatuses::DONE,
        ];

        return $this->saleRepository->updateByModel($data, $sale);
    }

    public function updateClicksCount($id)
    {
        $sale = $this->saleRepository->findById($id);

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($sale['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);


        $this->saleStatRepository->create([
            'user_id' => Auth::id(),
            'sale_id' => $sale['id'],
        ]);

        $data = [
            'clicks_count' => (int)$sale['clicks_count'] + 1,
        ];

        return $this->saleRepository->updateByModel($data, $sale);
    }
}
