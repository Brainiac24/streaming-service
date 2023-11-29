<?php

namespace App\Services\Payment;

use App\Enums\Payment\PaymentRequisitesServiceEnum;
use App\Models\Payment\PaymentRequisite;
use App\Repositories\Payment\PaymentRequisiteRepository;
use App\Repositories\Project\ProjectRepository;

class PaymentRequisiteService
{
    public function __construct(
        public PaymentRequisiteRepository $paymentRequisiteRepository,
        public ProjectRepository          $projectRepository,
        public CloudPaymentService        $cloudPaymentService
    )
    {
    }

    public function create(array $request): PaymentRequisite
    {
        if ($request['service'] === PaymentRequisitesServiceEnum::CLOUD_PAYMENTS->value) {
            $this->cloudPaymentService->updateNotifyType($request['public_api_key'], $request['private_api_key']);
        }

        $project = $this->projectRepository->findByIdForCurrentAuthedUser($request['project_id']);

        return $this->paymentRequisiteRepository->create([
            'service' => $request['service'],
            'project_id' => $project->id,
            'public_api_key' => $request['public_api_key'],
            'private_api_key' => $request['private_api_key']
        ]);
    }
}
