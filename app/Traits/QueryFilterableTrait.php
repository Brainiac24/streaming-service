<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait QueryFilterableTrait
{
    public function scopeWithQueryFilters(Builder $query, $sortBy = null, $sortField = null)
    {
        $sortBy = request('sortBy', $sortBy ?? 'asc');

        $sortField = request('sortField', $sortField);

        if (!$sortField && $sortBy) {
            $query->orderBy($query->getModel()->getTable() . '.id', $sortBy);
        } else if ($sortBy) {
            $query->orderBy($sortField, $sortBy);
        }

        $queryParams = request('filter', []);

        foreach ($queryParams as $queryKey => $queryValue) {
            if (is_array($queryValue)) {
                foreach ($queryValue as $key => $value) {
                    $field = $queryKey . '.' . $key;
                    $this->setFilter($field, $value, $query);
                }
            } else {
                $this->setFilter($queryKey, $queryValue, $query);
            }
        }
    }

    public function setFilter($key, $value, Builder $query)
    {
        if (str_ends_with($key, '_at')) {
            $value = Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
        }
        $query->where($key, $value);
    }
}
