<?php

namespace App\Filament\Resources\Merchants\Pages;

use App\Filament\Resources\MerchantResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMerchant extends CreateRecord
{
    protected static string $resource = MerchantResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Merchant Created')
            ->body('The merchant has been created successfully.');
    }
}
