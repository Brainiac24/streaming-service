<?php

namespace App\Http\Resources\Contact;

use App\Http\Resources\BaseJsonResource;
use Auth;

class ContactListResource extends BaseJsonResource
{
    public function __construct($contactList)
    {
        $headerRequired = [
            'id' => __('ID'),
            'email' => __('Email'),
            'name' => __('Name'),
            'lastname' => __('Lastname'),
            'contact_group_name' => __('ContactGroupName'),
        ];

        $headerOptional = [];

        foreach ($contactList as $contact) {
            if (isset($contact['data_json']) && !empty($contact['data_json'])) {
                foreach ($contact['data_json'] as $key => $value) {
                    if (!array_key_exists($key, $headerOptional)) {
                        $headerOptional[$key] = __($key);
                    }
                }
            }
        }
        $headers = array_merge($headerRequired, $headerOptional);

        $data = [];
        foreach ($contactList as $contact) {
            if (!isset($data[$contact['id']])) {
                $data[$contact['id']] = [
                    'id' => $contact['id'],
                    'email' => (!is_null($contact['email']) ? $contact['email'] : "Guest"),
                    'name' => $contact['name'],
                    'lastname' => $contact['lastname'],
                    'contact_group_name' => $contact['contact_group_name'],
                ];
                if (isset($contact['data_json']) && !empty($contact['data_json'])) {
                    $data_json = $contact['data_json'];
                    foreach ($headerOptional as $column => $columnValue) {
                        if (array_key_exists($column, $data_json)) {
                            $data[$contact['id']][$column] = $data_json[$column];
                        } else {
                            $data[$contact['id']][$column] = '';
                        }
                    }
                }
            }
        }

        $this->data = [$headers] + $data;
    }
}
