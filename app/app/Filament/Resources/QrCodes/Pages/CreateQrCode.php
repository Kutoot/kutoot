<?php

namespace App\Filament\Resources\QrCodes\Pages;

use App\Filament\Resources\QrCodeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQrCode extends CreateRecord
{
    protected static string $resource = QrCodeResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('QR Code Created')
            ->body('The QR code has been created successfully.');
    }
}
