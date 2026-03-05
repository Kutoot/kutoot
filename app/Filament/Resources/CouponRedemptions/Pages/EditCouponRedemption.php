<?php

namespace App\Filament\Resources\CouponRedemptions\Pages;

use App\Filament\Resources\CouponRedemptionResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCouponRedemption extends EditRecord
{
    protected static string $resource = CouponRedemptionResource::class;

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
            ->title('Coupon Redemption Updated')
            ->body('The coupon redemption has been updated successfully.');
    }
}
