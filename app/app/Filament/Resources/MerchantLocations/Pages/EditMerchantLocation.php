<?php

namespace App\Filament\Resources\MerchantLocations\Pages;

use App\Filament\Resources\MerchantLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMerchantLocation extends EditRecord
{
    protected static string $resource = MerchantLocationResource::class;

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
            ->title('Merchant Location Updated')
            ->body('The merchant location has been updated successfully.');
    }
}
