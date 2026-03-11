<?php

namespace App\Filament\Resources\CouponCategories\Pages;

use App\Filament\Resources\CouponCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCouponCategory extends EditRecord
{
    protected static string $resource = CouponCategoryResource::class;

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
            ->title('Coupon Category Updated')
            ->body('The coupon category has been updated successfully.');
    }
}
