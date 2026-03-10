<?php

namespace App\Filament\Resources\CharityPartners\Pages;

use App\Filament\Resources\CharityPartners\CharityPartnerResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateCharityPartner extends CreateRecord
{
    protected static string $resource = CharityPartnerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'Charity Partner';

        return $data;
    }

    protected function afterCreate(): void
    {
        Cache::forget('sponsors:active');
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Charity Partner Created')
            ->body('The charity partner has been created successfully.');
    }
}
