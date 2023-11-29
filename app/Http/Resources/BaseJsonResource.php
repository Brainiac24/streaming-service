<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseJsonResource extends JsonResource
{
    public function __construct(public $code = 0, public $message = 'Success', public $data = [], public $errors = [], public $meta = [], public $scope = [], public $mutation = [], public $pagination = null)
    {
        if (
            !empty($this->data) &&
            $this->data instanceof LengthAwarePaginator
        ) {

            if ($this->data->hasPages()) {
                $this->pagination = [
                    'current_page' => $this->data->currentPage(),
                    'last_page' => $this->data->lastPage(),
                    'per_page' => $this->data->perPage(),
                    'total' => $this->data->total(),
                    'first_item' => $this->data->firstItem(),
                    'last_item' => $this->data->lastItem(),
                ];
            }

            $this->data = $this->data->toArray()['data'];
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request = null)
    {
        return [
            'code' => $this->code,
            !$this->message ?: 'message' => $this->message,
            !$this->scope ?: 'scope' => $this->scope,
            !$this->mutation ?: 'mutation' => $this->mutation,
            !$this->errors ?: 'errors' => $this->errors,
            !$this->data ?: 'data' => $this->data,
            !$this->meta ?: 'meta' => $this->meta,
            !$this->pagination ?: 'pagination' => $this->pagination,
        ];
    }
}
