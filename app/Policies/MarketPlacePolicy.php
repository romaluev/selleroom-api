<?php

namespace App\Policies;

use App\Models\MarketPlace;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use function Symfony\Component\Translation\t;

class MarketPlacePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketPlace $market_place): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketPlace $market_place): bool
    {
        if($user->hasRoles('Super user')) return true;
        return $user->company_id == $market_place->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketPlace $market_place): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketPlace $market_place): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketPlace $market_place): bool
    {
        return false;
    }
}
