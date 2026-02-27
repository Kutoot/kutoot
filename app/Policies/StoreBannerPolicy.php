<?php

namespace App\Policies;

use App\Models\StoreBanner;
use App\Models\User;

class StoreBannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-store-banner');
    }

    public function view(User $user, StoreBanner $storeBanner): bool
    {
        return $user->can('view-store-banner');
    }

    public function create(User $user): bool
    {
        return $user->can('create-store-banner');
    }

    public function update(User $user, StoreBanner $storeBanner): bool
    {
        return $user->can('update-store-banner');
    }

    public function delete(User $user, StoreBanner $storeBanner): bool
    {
        return $user->can('delete-store-banner');
    }

    public function restore(User $user, StoreBanner $storeBanner): bool
    {
        return $user->can('restore-store-banner');
    }

    public function forceDelete(User $user, StoreBanner $storeBanner): bool
    {
        return $user->can('force-delete-store-banner');
    }
}
