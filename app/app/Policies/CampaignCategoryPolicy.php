<?php

namespace App\Policies;

use App\Models\CampaignCategory;
use App\Models\User;

class CampaignCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-campaign-category');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CampaignCategory $campaignCategory): bool
    {
        return $user->can('view-campaign-category');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-campaign-category');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CampaignCategory $campaignCategory): bool
    {
        return $user->can('update-campaign-category');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CampaignCategory $campaignCategory): bool
    {
        return $user->can('delete-campaign-category');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CampaignCategory $campaignCategory): bool
    {
        return $user->can('restore-campaign-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CampaignCategory $campaignCategory): bool
    {
        return $user->can('force-delete-campaign-category');
    }
}
