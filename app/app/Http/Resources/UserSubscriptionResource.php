<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\UserSubscription
 */
class UserSubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'days_remaining' => app(\App\Services\SubscriptionService::class)
                ->calculateDaysRemaining($this->expires_at),
            'plan' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
