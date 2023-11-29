<?php

namespace App\Http\Resources\EventTicket;

use App\Constants\EventTicketStatuses;
use App\Http\Resources\BaseJsonResource;
use Carbon\Carbon;

class FileEventTicketListResource extends BaseJsonResource
{
    public function __construct($tickets)
    {
        $this->data[] = [
            __('Date'),
            __('Ticket'),
            __('Email'),
            __('Name'),
            __('Status'),
        ];
        $statuses = [
            EventTicketStatuses::ACTIVE => __('Active'),
            EventTicketStatuses::USED => __('Used'),
            EventTicketStatuses::BANNED => __('Banned'),
            EventTicketStatuses::INACTIVE => __('Inactive'),
            EventTicketStatuses::RESERVED => __('Reserved'),
        ];

        foreach ($tickets as $ticket) {
            $this->data[] = [
                Carbon::create($ticket["created_at"])->format("d.m.Y H:i:s"),
                $ticket['ticket'],
                $ticket['user_email'],
                trim($ticket['user_name'] . ' ' . $ticket['user_lastname']),
                $statuses[$ticket['event_ticket_status_id']]
            ];
        }
    }
}
