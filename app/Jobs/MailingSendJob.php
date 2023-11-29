<?php

namespace App\Jobs;

use App\Constants\CacheKeys;
use App\Constants\MailingStatuses;
use App\Http\Controllers\EventSessionController;
use App\Repositories\Contact\ContactRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Mailing\MailingRepository;
use App\Repositories\MailingRequisite\MailingRequisiteRepository;
use App\Repositories\MessageTemplate\MessageTemplateRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\EventSession\EventSessionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MailingSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $mailingId)
    {
        //
    }


    public function handle()
    {

        Log::info("-------------------MailingSendJob STARTED------------------");
        if (CacheServiceFacade::get(CacheKeys::isJobCanceledByUuidKey($this->job->getJobId()))) {
            $this->job->delete();
        }

        $mailingRepository = app()->make(MailingRepository::class);
        $mailing = $mailingRepository->findById($this->mailingId);

        if ($mailing && $mailing['mailing_status_id'] != MailingStatuses::ACTIVE) {
            $this->job->delete();
        }

        $contactRepository = app()->make(ContactRepository::class);

        $mailingRequisiteRepository = app()->make(MailingRequisiteRepository::class);
        $messageTemplateRepository = app()->make(MessageTemplateRepository::class);
        $eventSessionService = app()->make(EventSessionService::class);
        $eventRepository = app()->make(EventRepository::class);

        $eventSession = $eventSessionService->findByIdWithEvent($mailing['event_session_id']);

        if ($mailing['is_default']) {
            $users = $eventRepository->getUserMembersByEventId($mailing['event_id']);
        } else {
            $users = $contactRepository->allByEventIdAndContactGroupIdForJob($mailing['event_id'], $mailing['contact_group_id']);
        }

        $mailingRequisite = $mailingRequisiteRepository->findByIdNoFail($mailing['mailing_requisite_id']);
        $messageTemplate = $messageTemplateRepository->findByIdNoFail($mailing['message_template_id']);

        if (empty($mailingRequisite)) {
            $mailingRequisite = config('mail.mailers.smtp');
        }

        if (count($users) > 0) {
            $mailingRepository = app()->make(MailingRepository::class);
            $response = null;
            try {

                Carbon::setLocale('ru');
                $response = Http::withHeaders([
                    'X-Auth-Token' => config('auth.message_broker_x_auth_token')
                ])->post(config('app.message_broker_host') . '/api/v1/email-notification', [
                    'message' => [
                        'title' => $mailing['message_title'],
                        "html_template" => view(
                            $messageTemplate['blade_path'],
                            [
                                'event_name' =>  $eventSession['name'],
                                'event_cover_url' => env('APP_URL') . $eventSession['cover_img_path'],
                                'event_session_url' => env('APP_URL') . DIRECTORY_SEPARATOR . ($eventSession['project_link'] ?? 'event') . DIRECTORY_SEPARATOR . $eventSession['event_link'] . DIRECTORY_SEPARATOR . $eventSession['code'],
                                'event_session_date' => Carbon::parse($eventSession['stream_start_at'])->isoFormat('D MMMM в HH:mm мск'),
                            ]
                        )->render(),
                    ],
                    'smtp_credentials' => $mailingRequisite,
                    'users' => $users
                ]);

                if ($response->ok()) {
                    $mailingRepository->update([
                        'job_uuid' => $response->json()['data']['id'],
                        'data_json' => $response->json()['data'],
                        'mailing_status_id' => MailingStatuses::IN_PROCESS
                    ], $mailing['id']);
                } else {
                    $mailingRepository->update([
                        'data_json' => [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ],
                        'mailing_status_id' => MailingStatuses::NOT_AVAILABLE
                    ], $mailing['id']);

                    $this->release(30);
                }
            } catch (\Throwable $th) {
                Log::info("MailingSendJob - " . $th->getMessage(), $th->getTrace());
                $mailingRepository->update([
                    'data_json' => [
                        'message' => $th->getMessage(),
                        'trace' => $th->getTraceAsString(),
                        'status' => $response?->status(),
                        'body' => $response?->body()
                    ],
                    'mailing_status_id' => MailingStatuses::NOT_AVAILABLE
                ], $mailing['id']);
                $this->release(30);
            }
        }

        Log::info("-------------------MailingSendJob ENDED------------------");
    }
}
