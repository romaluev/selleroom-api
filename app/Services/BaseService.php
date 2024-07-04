<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Throwable;

class BaseService
{

    protected ?BaseRepository $repository = null;

    /**
     * @param array $data
     * @return LengthAwarePaginator
     * @throws Throwable
     */
    public function paginatedList($data = [], $all = false): LengthAwarePaginator|Collection
    {
        if ($all)
            return $this->repository->getAllList($data);
        return $this->repository->paginatedList($data);
    }

    public function getAllList($data = []): Collection
    {
        return $this->repository->getAllList($data);
    }


    /**
     * @param $data
     * @return Model|Model[]|Builder|Builder[]|Collection|null
     * @throws Throwable
     */
    public function createModel($data): array |Collection|Builder|Model|null|JsonResponse
    {
        /**
         * @var User auth()->user()
         */
        $user = auth()->user();
        if ($user->can('create', $this->repository->getModel()))
            return $this->getRepository()->create(array_merge($data));
        else return self::permissionDenied();
    }

    /**
     * @return BaseRepository
     * @throws Throwable
     */
    protected function getRepository(): BaseRepository
    {
        throw_if(!$this->repository, get_class($this) . ' repository property not implemented');
        return $this->repository;
    }

    /**
     * @param $data
     * @param $id
     * @return Model|Model[]|Builder|Builder[]|Collection|null
     * @throws Throwable
     */
    public function updateModel($data, $id): array |Collection|Builder|Model|null|JsonResponse
    {
        /**
         * @var User auth()->user()
         */
        $user = auth()->user();
        if ($user->can('update', $this->repository->findById($id)))
            return $this->getRepository()->update($data, $id);
        else return self::permissionDenied();
    }

    /**
     * @param $id
     * @return array|Builder|Builder[]|Collection|Model|Model[]|JsonResponse
     * @throws Throwable
     */
    public function deleteModel($id)
    {
        return $this->getRepository()->delete($id);
    }

    /**
     * @param $id
     * @return Model|Model[]|Builder|Builder[]|Collection|null
     * @throws Throwable
     */
    public function getModelById($id): Model|array |Collection|Builder|null
    {
        return $this->getRepository()->findById($id);
    }
    public static function permissionDenied()
    {
        return response()->json([
            'message' => 'Permission denied'
        ], 403);
    }
}
