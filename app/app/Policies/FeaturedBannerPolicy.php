<?php

namespace App\Policies;

use App\Models\FeaturedBanner;
use App\Models\User;

class FeaturedBannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-featured-banner');
    }

    public function view(User $user, FeaturedBanner $featuredBanner): bool
    {
        return $user->can('view-featured-banner');
    }

    public function create(User $user): bool
    {
        return $user->can('create-featured-banner');
    }

    public function update(User $user, FeaturedBanner $featuredBanner): bool
    {
        return $user->can('update-featured-banner');
    }

    public function delete(User $user, FeaturedBanner $featuredBanner): bool
    {
        return $user->can('delete-featured-banner');
    }

    public function restore(User $user, FeaturedBanner $featuredBanner): bool
    {
        return $user->can('restore-featured-banner');
    }

    public function forceDelete(User $user, FeaturedBanner $featuredBanner): bool
    {
        return $user->can('force-delete-featured-banner');
    }
}
