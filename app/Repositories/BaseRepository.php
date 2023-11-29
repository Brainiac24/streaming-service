<?php

namespace App\Repositories;

use App\Exceptions\UnknownException;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class BaseRepository
{
    public function __construct(public Model $model)
    {
    }

    public function setTable($tableName)
    {
        return $this->model->setTable($tableName);
    }

    public function all()
    {
        return $this->model->get();
    }

    public function allWithPagination(array | string $columns = ['*'], string $pageName = "page", int | null $page = null, Closure | int | null $perPage = 15)
    {
        $perPage = (int) Request::get('perPage', $perPage);
        return $this->model->withQueryFilters()->paginate($perPage, $columns, $pageName, $page);
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByIdNoFail($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function createOrUpdate(array $data, array $condition)
    {
        return $this->model->updateOrCreate($condition, $data);
    }

    public function createWithTableNamePostfix(array $data, $tableNamePostfix)
    {
        return $this->model->setTable($this->model->getTable() . '_' . $tableNamePostfix)->create($data);
    }

    public function insert(array $data)
    {
        $insertedCount = $this->model->insert($data);
        if ($insertedCount != count($data)) {
            throw new UnknownException('Unknown error: Could not insert data!');
        };

        return $insertedCount;
    }

    public function insertAndRetrieve(array $data)
    {
        $models = [];
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                $models[] = $this->model->create($item);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return $models;
    }

    public function update(array $data, $id)
    {
        $model = $this->model->findOrFail($id);

        if (isset($data['config_json'])) {
            try {
                $data['config_json'] = array_replace_recursive($model->config_json, $data['config_json']);
            } catch (\Throwable $th) {
                logger()->info("Couldn't merge config_json with existing config_json", $th->getTrace());
            }
        }

        $model->update($data);
        return $model;
    }

    public function updateByModel(array $data, $model)
    {
        if (isset($data['config_json'])) {
            try {
                $data['config_json'] = array_replace_recursive($model->config_json, $data['config_json']);
            } catch (\Throwable $th) {
                logger()->info("Couldn't merge config_json with existing config_json", $th->getTrace());
            }
        }
        $model->update($data);
        return $model;
    }

    public function delete($id)
    {
        if ($this->model->where('id', $id)->delete() == 0) {
            throw new ModelNotFoundException();
        }
        return true;
    }

    public function deleteMany(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function deleteByModel($model)
    {
        return (bool) $model->delete();
    }

    public function updateMany(array $data, array $ids = [])
    {
        $model = $this->model;

        if (!empty($ids)) {
            $model->whereIn('id', $ids);
        }

        $model->update($data);
        return $model;
    }
}
