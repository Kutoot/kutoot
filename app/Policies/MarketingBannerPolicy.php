<?php

namespace App\Policies;

use App\Models\MarketingBanner;
use App\Models\User;

class MarketingBannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-marketing-banner');
    }

    public function view(User $user, MarketingBanner $marketingBanner): bool
    {
        return $user->can('view-marketing-banner');
    }

    public function create(User $user): bool
    {
        return $user->can('create-marketing-banner');
    }

    public function update(User $user, MarketingBanner $marketingBanner): bool
    {
        return $user->can('update-marketing-banner');
    }

    public function delete(User $user, MarketingBanner $marketingBanner): bool
    {
        return $user->can('delete-marketing-banner');
    }

    public function restore(User $user, MarketingBanner $marketingBanner): bool
    {
        return $user->can('restore-marketing-banner');
    }

    public function forceDelete(User $user, MarketingBanner $marketingBanner): bool
    {
        return $user->can('force-delete-marketing-banner');
    }
}
