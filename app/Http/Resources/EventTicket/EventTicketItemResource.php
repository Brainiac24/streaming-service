<?php

namespace App\Http\Resources\EventTicket;

use App\Http\Resources\BaseJsonResource;

class EventTicketItemResource extends BaseJsonResource
{
    public function __construct($data,)
    {
        $fullname = trim($data['user_name'] . ' ' . $data['user_lastname']);
        $this->data = [
            'id' => $data['id'],
            'ticket' => $data['ticket'],
            'event_id' => $data['event_id'],
            'user_id' => $data['user_id'],
            'event_ticket_status_id' => $data['event_ticket_status_id'],
            'event_ticket_type_id' => $data['event_ticket_type_id'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'fullname' => $fullname,
            'email' => $data['user_email'],
            'is_verified' => (bool)$data['user_is_verified'],
            'avatar_path' => $data['user_avatar_path'],
            'is_guest' => !(bool)$fullname,
        ];
    }
}
