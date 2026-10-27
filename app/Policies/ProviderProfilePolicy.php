<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProviderProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProviderProfilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProviderProfile');
    }

    public function view(AuthUser $authUser, ProviderProfile $providerProfile): bool
    {
        return $authUser->can('View:ProviderProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProviderProfile');
    }

    public function update(AuthUser $authUser, ProviderProfile $providerProfile): bool
    {
        return $authUser->can('Update:ProviderProfile');
    }

    public function delete(AuthUser $authUser, ProviderProfile $providerProfile): bool
    {
        return $authUser->can('Delete:ProviderProfile');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProviderProfile');
    }

    public function restore(AuthUser $authUser, ProviderProfile $providerProfile): bool
    {
        return $authUser->can('Restore:ProviderProfile');
    }

    public function forceDelete(AuthUser $authUser, ProviderProfile $providerProfile): bool
    {
        return $authUser->can('ForceDelete:ProviderProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProviderProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProviderProfile');
    }

    public function replicate(AuthUser $authUser, ProviderProfile $providerProfile): bool
    {
        return $authUser->can('Replicate:ProviderProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProviderProfile');
    }

}