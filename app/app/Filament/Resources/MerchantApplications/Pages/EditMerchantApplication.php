<?php

namespace App\Filament\Resources\MerchantApplications\Pages;

use App\Filament\Resources\MerchantApplications\MerchantApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMerchantApplication extends EditRecord
{
    protected static string $resource = MerchantApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
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
            ->title('Merchant Application Updated')
            ->body('The merchant application has been updated successfully.');
    }
}
