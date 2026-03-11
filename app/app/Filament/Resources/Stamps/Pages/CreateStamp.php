<?php

namespace App\Filament\Resources\Stamps\Pages;

use App\Filament\Resources\StampResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStamp extends CreateRecord
{
    protected static string $resource = StampResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Stamp Created')
            ->body('The stamp has been created successfully.');
    }
}
