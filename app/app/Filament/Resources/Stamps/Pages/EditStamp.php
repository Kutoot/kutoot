<?php

namespace App\Filament\Resources\Stamps\Pages;

use App\Filament\Resources\StampResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStamp extends EditRecord
{
    protected static string $resource = StampResource::class;

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
            ->title('Stamp Updated')
            ->body('The stamp has been updated successfully.');
    }
}
