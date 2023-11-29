<?php

namespace App\Http\Resources\Role;

use App\Http\Resources\BaseJsonResource;

class RoleListResource extends BaseJsonResource
{
    public function __construct($data)
    {

        parent::__construct(data: $data);
        $this->data = [];

        if ($data) {
            foreach ($data as $item) {
                $this->data[] = [
                    'id' => $item['id'] ?? $item['role_id'],
                    'name' => __($item['role_name'] ?? $item['name'] ?? $item['name'] ?? null),
                    'label' => __($item['label'] ?? $item['role_display_name'] ?? $item['display_name'] ?? null),
                ];
            }
        }
    }
}
