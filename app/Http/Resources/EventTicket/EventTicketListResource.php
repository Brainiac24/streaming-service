<?php

namespace App\Http\Resources\EventTicket;

use App\Http\Resources\BaseJsonResource;

class EventTicketListResource extends BaseJsonResource
{
    public function __construct($data)
    {
        parent::__construct(data: $data);
        $this->data = [];

        foreach ($data as $item) {
            $fullname = trim($item['user_name'] . ' ' . $item['user_lastname']);
            $this->data[] = [
                'id' => $item['id'],
                'ticket' => $item['ticket'],
                'event_id' => $item['event_id'],
                'user_id' => $item['user_id'],
                'event_ticket_status_id' => $item['event_ticket_status_id'],
                'event_ticket_type_id' => $item['event_ticket_type_id'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
                'fullname' => $fullname,
                'email' => $item['user_email'],
                'contact_email' => $item['user_contact_email'] ?? '',
                'is_verified' => $item['user_is_verified'],
                'avatar_path' => $item['user_avatar_path'],
                'is_guest' => !(bool)$fullname,
            ];
        }
    }
}
