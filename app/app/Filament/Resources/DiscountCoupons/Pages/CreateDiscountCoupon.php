<?php

namespace App\Filament\Resources\DiscountCoupons\Pages;

use App\Filament\Resources\DiscountCouponResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountCoupon extends CreateRecord
{
    protected static string $resource = DiscountCouponResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Discount Coupon Created')
            ->body('The discount coupon has been created successfully.');
    }
}
