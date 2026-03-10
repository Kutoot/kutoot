<?php

namespace App\Filament\Resources\DiscountCoupons\Pages;

use App\Filament\Resources\DiscountCouponResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDiscountCoupon extends EditRecord
{
    protected static string $resource = DiscountCouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Discount Coupon Updated')
            ->body('The discount coupon has been updated successfully.');
    }
}
