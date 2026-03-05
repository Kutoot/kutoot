<?php

namespace App\Filament\Resources\CouponCategories\Pages;

use App\Filament\Resources\CouponCategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCouponCategory extends CreateRecord
{
    protected static string $resource = CouponCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Coupon Category Created')
            ->body('The coupon category has been created successfully.');
    }
}
