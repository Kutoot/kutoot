<?php

namespace App\Filament\Resources\MerchantLocations\Pages;

use App\Filament\Resources\MerchantLocationResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMerchantLocation extends CreateRecord
{
    protected static string $resource = MerchantLocationResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Merchant Location Created')
            ->body('The merchant location has been created successfully.');
    }
}
