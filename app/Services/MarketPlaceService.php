<?php

namespace App\Services;

use App\Repositories\MarketPlaceRepository;

class MarketPlaceService extends BaseService
{
    public function __construct(MarketPlaceRepository $repository)
    {
        $this->repository = $repository;
    }

}
