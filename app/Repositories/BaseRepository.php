<?php

namespace App\Repositories;

use App\Constants\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class BaseRepository
{
    protected Model $modelClass;

    /**
     * @param Model $modelClass
     */
    public function __construct(Model $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @throws Throwable
     */
    protected function query(): Builder|Model
    {
        /** @var Model $class */
        $query = $this->getModel()->query();

        if (!is_null(auth()->user()) && auth()->user()->publisher_id && method_exists($this->getModel(), 'scopeRole')) {
            $query->role(auth()->user());
        }

        return $query->orderByDesc('id');
    }

    /**
     * @return Model
     * @throws Throwable
     */
    public function getModel(): Model
    {
        return $this->modelClass;
    }

    /**
     * @throws Throwable
     */
    public function paginatedList($data = [], array|string $with = null): LengthAwarePaginator
    {
        $query = $this->query();
        if (method_exists($this->getModel(), 'scopeFilter')) {
            $query->filter($data);
        }

        if (!is_null($with)) {
            $query->with($with);
        }

        return $query->paginate(15);
    }

    public function getAllList($data = [], array|string $with = null): Collection
    {
        $query = $this->query();
        if (method_exists($this->getModel(), 'scopeFilter')) {
            $query->filter($data);
        }

        if (!is_null($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    /**
     * @param $data
     * @return Model|Model[]|Builder|Builder[]|Collection|null
     * @throws Throwable
     */
    public function create($data): array|Collection|Builder|Model|null
    {
        //        dd($data);
        $model = $this->getModel();
        foreach ($data as $item => $value) {
            if (!in_array($item, $model->extra)) {
                $model->{$item} = $value;
            }
        }
        $model->save();
        return $model;
    }

    /**
     * @param $data
     * @param $id
     * @return Model|Model[]|Builder|Builder[]|Collection|null
     * @throws Throwable
     */
    public function update($data, $id): Model|array|Collection|Builder|null
    {
        $model = $this->query()->whereId($id)->first();

        foreach ($data as $item => $value) {

            $model->{$item} = $value;
        }
        $model->save();
        return $model;
    }

    /**
     * @param $id
     * @return Model|Model[]|Builder|Builder[]|Collection|null
     * @throws Throwable
     */
    public function findById($id, $relations = []): Model|array|Collection|Builder|null
    {
        if (!empty($relations)) {
            return $this->query()->with($relations)->findOrFail($id);
        }
        return $this->query()->findOrFail($id);
    }

    /**
     * @param $id
     * @return array|Builder|Builder[]|Collection|Model|Model[]
     * @throws Throwable
     */
    public function delete($id): array|Builder|Collection|Model
    {
        /**
         * @var User auth()->user()
         */
        $user = auth()->user();
        $model = $this->findById($id);
        if ($user->can('delete', $model)) {
            $model->delete();
            return $model;
        } else {
            return self::permissionDenied();
        }
    }

    public static function permissionDenied()
    {
        return response()->json([
            'message' => 'Permission denied'
        ], 403);
    }
}
