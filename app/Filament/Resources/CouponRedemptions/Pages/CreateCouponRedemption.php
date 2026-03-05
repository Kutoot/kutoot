<?php

namespace App\Filament\Resources\CouponRedemptions\Pages;

use App\Filament\Resources\CouponRedemptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCouponRedemption extends CreateRecord
{
    protected static string $resource = CouponRedemptionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Coupon Redemption Created')
            ->body('The coupon redemption has been created successfully.');
    }
}
