<?php

namespace App\Policies;

use App\Models\PortfolioItem;
use App\Models\User;

class PortfolioItemPolicy
{
    public function manage(User $user, PortfolioItem $portfolioItem): bool
    {
        return $user->id === $portfolioItem->providerProfile->user_id;
    }
}
