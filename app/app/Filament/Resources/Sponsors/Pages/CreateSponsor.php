<?php

namespace App\Filament\Resources\Sponsors\Pages;

use App\Filament\Resources\Sponsors\SponsorResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSponsor extends CreateRecord
{
    protected static string $resource = SponsorResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Sponsor Created')
            ->body('The sponsor has been created successfully.');
    }
}
