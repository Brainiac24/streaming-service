<?php

namespace App\Repositories\Payment;

use App\Enums\Payment\PaymentRequisitesServiceEnum;
use App\Models\Payment\PaymentRequisite;
use App\Repositories\BaseRepository;
use Auth;
use Illuminate\Database\Eloquent\Collection;

class PaymentRequisiteRepository extends BaseRepository
{
    public function __construct(public PaymentRequisite $paymentRequisite)
    {
        parent::__construct($paymentRequisite);
    }

    public function getUserPaymentRequisiteList(): Collection
    {
        return $this->paymentRequisite
            ->join('projects', 'projects.id', 'payment_requisites.project_id')
            ->where('projects.user_id', Auth::user()->id)
            ->select('payment_requisites.*')
            ->get();
    }

    public function getUserPaymentRequisiteById(int $paymentRequisiteId): ?PaymentRequisite
    {
        return $this->paymentRequisite
            ->join('projects', 'projects.id', 'payment_requisites.project_id')
            ->where('payment_requisites.id', $paymentRequisiteId)
            ->where('projects.user_id', Auth::user()->id)
            ->select('payment_requisites.*')
            ->first();
    }

    public function getPaymentRequisiteByProjectIdAndAuthUserId(int $projectId): ?PaymentRequisite
    {
        return $this->paymentRequisite
            ->join('projects', 'projects.id', 'payment_requisites.project_id')
            ->where('projects.id', $projectId)
            ->where('projects.user_id', Auth::user()->id)
            ->select('payment_requisites.*')
            ->first();
    }

    public function getPaymentRequisiteByProjectIdAndService(int $projectId, PaymentRequisitesServiceEnum $paymentRequisitesServiceEnum): PaymentRequisite
    {
        return $this->paymentRequisite
            ->join('projects', 'projects.id', 'payment_requisites.project_id')
            ->where('projects.id', $projectId)
            ->where('payment_requisites.service', $paymentRequisitesServiceEnum->value)
            ->select('payment_requisites.*')
            ->first();

    }
}
