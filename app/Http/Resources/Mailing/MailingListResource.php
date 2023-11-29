<?php

namespace App\Http\Resources\Mailing;

use App\Http\Resources\BaseJsonResource;
use Illuminate\Database\Eloquent\Collection;

class MailingListResource extends BaseJsonResource
{
    public function __construct(Collection $mailings)
    {
        parent::__construct(data: $mailings);
        $this->data = [];

        foreach ($mailings as $mailing) {

            $this->data[] = [
                "id" => $mailing->id,
                "job_uuid" => $mailing->job_uuid,
                "data_json" => $mailing->data_json,
                "created_at" => $mailing->created_at,
                "updated_at" => $mailing->updated_at,
                "mailing_requisite_id" => $mailing->mailing_requisite_id,
                'mailing_requisites_host' => $mailing->requisites_host,
                "message_template_id" => $mailing->message_template_id,
                "message_title" => $mailing->message_title,
                "event_id" => $mailing->event_id,
                "event_session_id" => $mailing->event_session_id,
                "is_default" => $mailing->is_default,
                "delay_count" => $mailing->delay_count,
                "send_at" => $mailing->send_at,
                "contact_group_id" => $mailing->contact_group_id,
                "contact_group_name" => $mailing->contact_group_name,
                "mailing_status_name" => $mailing->mailing_status_name,
                "event_session_name" => $mailing->event_session_name,
                'progress' => empty($mailing->data_json) ? null : $mailing->data_json['progress'],
                'email_sended_count' => empty($mailing->data_json) ? null : $mailing->data_json['processedJobs'] - 3,
                'email_failed_count' => empty($mailing->data_json) ? null : $mailing->data_json['failedJobs'],
            ];
        }
    }
}
