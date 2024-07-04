<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreMarketPlaceRequest;
use App\Http\Requests\UpdateMarketPlaceRequest;
use App\Models\MarketPlace;
use App\Services\MarketPlaceService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Class MarketPlaceController
 * @package  App\Http\Controllers
 */
class MarketPlaceController extends \App\Http\Controllers\Controller
{
    private MarketPlaceService $service;

    public function __construct(MarketPlaceService $service)
    {
        $this->service = $service;
    }


    public function index(): LengthAwarePaginator
    {
        return $this->service->paginatedList();
    }

    /**
     * @param StoreMarketPlaceRequest $request
     * @return array|Builder|Collection|MarketPlace|Builder[]|MarketPlace[]
     * @throws Throwable
     */
    public function store(StoreMarketPlaceRequest $request): array|Builder|Collection|MarketPlace|\Illuminate\Http\JsonResponse
    {
        return $this->service->createModel($request->validated());
    }

    /**

     * @param $productId
     * @return JsonResponse
     * @throws Throwable
     */
    public function show($productId): Model
    {
        return $this->service->getModelById($productId);
    }

    /**
     * @param UpdateMarketPlaceRequest $request
     * @param int $productId
     * @return array|Builder|Builder[]|Collection|MarketPlace|MarketPlace[]
     * @throws Throwable
     */
    public function update(UpdateMarketPlaceRequest $request, int $productId): array|MarketPlace|Collection|Builder|\Illuminate\Http\JsonResponse
    {
        return $this->service->updateModel($request->validated(), $productId);
    }

    /**
     * @param int $productId
     * @return array|Builder|Builder[]|Collection|MarketPlace|MarketPlace[]
     * @throws Throwable
     */
    public function destroy(int $productId): array|Builder|Collection|MarketPlace
    {
        return $this->service->deleteModel($productId);
    }
}
