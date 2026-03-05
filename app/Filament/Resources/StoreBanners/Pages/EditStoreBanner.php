<?php

namespace App\Filament\Resources\StoreBanners\Pages;

use App\Filament\Resources\StoreBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStoreBanner extends EditRecord
{
    protected static string $resource = StoreBannerResource::class;

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
            ->title('Store Banner Updated')
            ->body('The store banner has been updated successfully.');
    }
}
