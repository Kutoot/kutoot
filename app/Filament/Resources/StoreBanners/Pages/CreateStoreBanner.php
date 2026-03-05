<?php

namespace App\Filament\Resources\StoreBanners\Pages;

use App\Filament\Resources\StoreBannerResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStoreBanner extends CreateRecord
{
    protected static string $resource = StoreBannerResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Store Banner Created')
            ->body('The store banner has been created successfully.');
    }
}
