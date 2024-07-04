<?php

namespace App\Repositories;

use App\Models\MarketPlace;

class MarketPlaceRepository extends BaseRepository
{
    public function __construct(MarketPlace $model)
    {
        parent::__construct($model);
    }
}
