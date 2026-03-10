<?php

namespace App\Filament\Resources\MerchantCategories\Pages;

use App\Filament\Resources\MerchantCategories\MerchantCategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMerchantCategory extends CreateRecord
{
    protected static string $resource = MerchantCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Merchant Category Created')
            ->body('The merchant category has been created successfully.');
    }
}
