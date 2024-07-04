<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Class ProductController
 * @package  App\Http\Controllers
 */
class ProductController extends \App\Http\Controllers\Controller
{
    private ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }


    public function index(): LengthAwarePaginator
    {
        return $this->service->paginatedList();
    }

    /**
     * @param StoreProductRequest $request
     * @return array|Builder|Collection|Product|Builder[]|Product[]
     * @throws Throwable
     */
    public function store(StoreProductRequest $request): array|Builder|Collection|Product|\Illuminate\Http\JsonResponse
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
     * @param UpdateProductRequest $request
     * @param int $productId
     * @return array|Builder|Builder[]|Collection|Product|Product[]
     * @throws Throwable
     */
    public function update(UpdateProductRequest $request, int $productId): array|Product|Collection|Builder|\Illuminate\Http\JsonResponse
    {
        return $this->service->updateModel($request->validated(), $productId);
    }

    /**
     * @param int $productId
     * @return array|Builder|Builder[]|Collection|Product|Product[]
     * @throws Throwable
     */
    public function destroy(int $productId): array|Builder|Collection|Product
    {
        return $this->service->deleteModel($productId);
    }
}
