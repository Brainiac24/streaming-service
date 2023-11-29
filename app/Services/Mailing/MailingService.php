<?php

namespace App\Services\Mailing;

use App\Constants\CacheKeys;
use App\Constants\MailingStatuses;
use App\Jobs\MailingSendJob;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Mailing\MailingRepository;
use App\Services\Cache\CacheServiceFacade;
use Carbon\Carbon;
use Illuminate\Bus\Dispatcher;

class MailingService
{
    public function __construct(public MailingRepository $mailingRepository, public EventSessionRepository $eventSessionRepository)
    {
    }
    public function create($mailingRequestData)
    {
        if (isset($mailingRequestData['delay_count']) && $mailingRequestData['delay_count'] !== null) {
            $stream = $this->eventSessionRepository->getStreamByEventSessionId($mailingRequestData['event_session_id']);
            $mailingRequestData['send_at'] = Carbon::parse($stream['start_at'])->subMinutes($mailingRequestData['delay_count'])->format('Y-m-d H:i:s');
        }

        if (!$mailingRequestData['mailing_status_id'] ?? true) {
            $mailingRequestData['mailing_status_id'] = MailingStatuses::ACTIVE;
        }

        $data = $this->mailingRepository->create($mailingRequestData);

        $this->dispathJob($data);

        return $data;
    }

    public function update($mailingRequestData, $id)
    {
        if ($mailingRequestData['delay_count'] ?? false) {
            $stream = $this->eventSessionRepository->getStreamByEventSessionId($mailingRequestData['event_session_id']);
            $mailingRequestData['send_at'] = Carbon::parse($stream['start_at'])->subMinutes($mailingRequestData['delay_count'])->format('Y-m-d H:i:s');
        }

        $data = $this->mailingRepository->update($mailingRequestData, $id);

        CacheServiceFacade::set(CacheKeys::isJobCanceledByUuidKey($data['job_uuid']), true);

        return $this->dispathJob($data);
    }

    public function dispathJob($mailing)
    {
        $uuid = app(Dispatcher::class)->dispatch((new MailingSendJob($mailing['id']))->delay($mailing['send_at'])); //
        $this->mailingRepository->update(['job_uuid' => $uuid], $mailing['id']);
        return $uuid;
    }

    public function updateCallback($mailingRequestData, $uuid)
    {
        $mailing = $this->mailingRepository->findByJobUuId($uuid);

        return $this->mailingRepository->updateByModel($mailingRequestData, $mailing);
    }
}
