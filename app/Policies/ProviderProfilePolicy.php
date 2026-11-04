<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProviderProfilePolicy
{
    use HandlesAuthorization;

    // Super admins bypass everything
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin') || $user->role === 'admin') {
            return true;
        }
        return null;
    }

    public function view(User $user, ProviderProfile $providerProfile): bool
    {
        return true; // Public profiles can be viewed by anyone
    }

    public function update(User $user, ProviderProfile $providerProfile): bool
    {
        // Only the owner of this provider profile can edit it
        return $user->id === $providerProfile->user_id;
    }

    public function delete(User $user, ProviderProfile $providerProfile): bool
    {
        return $user->id === $providerProfile->user_id;
    }
}
